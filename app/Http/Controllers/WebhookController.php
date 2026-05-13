<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Contribution;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Payout;
use App\Services\BlockchainService;
use App\Services\SmsService;
use App\Services\TelegramService;
use App\Services\TontineCycleService;
use Illuminate\Http\Request;
use App\Mail\TontineNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class WebhookController extends Controller
{
    protected $blockchain;
    protected $sms;
    protected $telegram;
    protected $cycles;

    public function __construct(BlockchainService $blockchain, SmsService $sms, TelegramService $telegram, TontineCycleService $cycles)
    {
        $this->blockchain = $blockchain;
        $this->sms = $sms;
        $this->telegram = $telegram;
        $this->cycles = $cycles;
    }

    #[OA\Post(
        path: "/api/v1/webhooks/fedapay",
        summary: "Webhook de réception des événements FedaPay",
        tags: ["Webhooks"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "event", type: "string", example: "transaction.approved"),
                    new OA\Property(property: "entity", type: "object")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Webhook traité avec succès"),
            new OA\Response(response: 401, description: "Signature invalide ou tentative frauduleuse")
        ]
    )]
    public function handleFedapay(Request $request)
    {
        // === SÉCURITÉ WEBHOOK (POINT 2) ===
        // On vérifie le secret FedaPay pour s'assurer que l'appel est authentique
        $webhookSecret = env('FEDAPAY_WEBHOOK_SECRET');
        $providedSecret = $request->header('X-Fedapay-Signature');

        if (app()->isProduction() && empty($webhookSecret)) {
            Log::critical('FEDAPAY_WEBHOOK_SECRET manquant en production : webhook refusé.');
            return response()->json(['error' => 'Server misconfigured'], 500);
        }

        if (! empty($webhookSecret)) {
            if ($providedSecret !== $webhookSecret) {
                Log::warning("Tentative de Webhook frauduleuse détectée de l'IP: " . $request->ip());
                return response()->json(['error' => 'Unauthorized Signature'], 401);
            }
        }

        $event = $request->input('event');
        $data = $request->input('entity');

        Log::info("Webhook FedaPay received: $event");

        if ($event === 'transaction.approved') {
            if (! is_array($data) || empty($data['id'])) {
                Log::warning('Webhook FedaPay transaction.approved sans identifiant de transaction.', [
                    'payload' => $request->all(),
                ]);

                return response()->json(['error' => 'Invalid webhook payload'], 422);
            }

            return $this->processConfirmedPayment($data);
        }

        return response()->json(['status' => 'ignored']);
    }

    protected function processConfirmedPayment($data)
    {
        return DB::transaction(function () use ($data) {
            $transactionId = (string) $data['id'];
            $contribution = Contribution::where('fedapay_transaction_id', $transactionId)->first();

            if (!$contribution) {
                Log::warning("Transaction {$transactionId} non trouvée.");
                return response()->json(['status' => 'not_found'], 404);
            }

            if ($contribution->status === 'confirmed') {
                return response()->json(['status' => 'already_processed']);
            }

            // Mise à jour de la cotisation
            $contribution->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            $group = $contribution->group;
            $user = $contribution->user;

            // Envoi Email de confirmation réel
            if ($user->email) {
                Mail::to($user->email)
                    ->locale($user->preferred_language ?? 'fr')
                    ->send(new TontineNotificationMail(
                        "Paiement Confirmé : " . $group->name,
                        __('messages.trust_score') . " (" . number_format($contribution->amount_fcfa, 0, ',', ' ') . " FCFA)"
                    ));
            }

            // Message système dans le chat (Social Proof)
            \App\Http\Controllers\MessageController::sendSystemMessage(
                $group->id, 
                "✅ " . $user->full_name . " a versé sa cotisation pour le cycle " . $contribution->cycle_number . ". Fiabilité au top !"
            );

            // Notification Telegram (Transparence de groupe)
            $this->telegram->sendPaymentAlert($user->full_name, $contribution->amount_fcfa, $group->name);

            $this->checkAndReleasePayout($group, $contribution->cycle_number);

            return response()->json(['status' => 'success']);
        });
    }

    protected function checkAndReleasePayout(Group $group, int $cycleNumber)
    {
        $confirmedCount = Contribution::where('group_id', $group->id)
            ->where('cycle_number', $cycleNumber)
            ->where('status', 'confirmed')
            ->count();

        if ($confirmedCount >= $group->max_members) {
            $totalCycleAmount = $group->contribution_amount * $group->max_members;
            
            $discount = 0;
            $nextBeneficiary = null;

            if ($group->payout_method === 'bidding') {
                $winningBid = Bid::where('group_id', $group->id)
                    ->where('cycle_number', $cycleNumber)
                    ->whereIn('status', ['won', 'pending'])
                    ->whereHas('user.memberships', function ($query) use ($group) {
                        $query->where('group_id', $group->id)
                            ->where('status', 'active')
                            ->where('has_received', false);
                    })
                    ->orderByRaw("CASE WHEN status = 'won' THEN 0 ELSE 1 END")
                    ->orderByDesc('discount_amount')
                    ->orderBy('created_at')
                    ->first();

                if ($winningBid) {
                    $nextBeneficiary = $group->members()
                        ->where('user_id', $winningBid->user_id)
                        ->where('status', 'active')
                        ->where('has_received', false)
                        ->first();

                    $discount = (float) $winningBid->discount_amount;

                    Bid::where('group_id', $group->id)
                        ->where('cycle_number', $cycleNumber)
                        ->update(['status' => 'lost']);

                    $winningBid->update(['status' => 'won']);
                } else {
                    Log::warning("Aucune enchere eligible pour le groupe {$group->id}, cycle {$cycleNumber}. Retour au mode sequentiel.");
                }
            }

            // Logique de sélection du bénéficiaire (séquentiel, aléatoire via positions, ou enchère)
            $nextBeneficiary ??= $group->members()
                ->where('has_received', false)
                ->where('status', 'active')
                ->orderBy('position', 'asc')
                ->first();

            if (! $nextBeneficiary) {
                Log::error("Payout impossible : aucun bénéficiaire éligible pour le groupe {$group->id}, cycle {$cycleNumber}.");
                return;
            }

            $payoutUserId = $nextBeneficiary->user_id;

            $insuranceAmount = ($totalCycleAmount * $group->insurance_percent) / 100;
            $payoutAmount = max(0, $totalCycleAmount - $insuranceAmount - $discount);
            $retainedAmount = $insuranceAmount + $discount;

            $payout = Payout::create([
                'group_id' => $group->id,
                'beneficiary_id' => $payoutUserId,
                'cycle_number' => $cycleNumber,
                'amount_fcfa' => $payoutAmount,
                'total_amount_fcfa' => $totalCycleAmount,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            if ($retainedAmount > 0) {
                $group->increment('insurance_fund', $retainedAmount);
            }

            GroupMember::where('group_id', $group->id)
                ->where('user_id', $payoutUserId)
                ->update([
                    'has_received' => true,
                    'cycle_received' => $cycleNumber,
                ]);

            // Blockchain
            $txHash = $this->blockchain->releasePayout($group->contract_address, $cycleNumber);
            $payout->update(['blockchain_tx_hash' => $txHash]);
            
            // Notification au bénéficiaire par Email
            $beneficiary = GroupMember::where('group_id', $group->id)->where('user_id', $payoutUserId)->first()->user;
            if ($beneficiary->email) {
                Mail::to($beneficiary->email)
                    ->locale($beneficiary->preferred_language ?? 'fr')
                    ->send(new TontineNotificationMail(
                        __('messages.your_turn'),
                        "Tontine: " . $group->name . " - " . number_format($payoutAmount, 0, ',', ' ') . " FCFA"
                    ));
            }

            // Message système dans le chat (Célébration)
            \App\Http\Controllers\MessageController::sendSystemMessage(
                $group->id, 
                "🏆 FÉLICITATIONS ! " . $beneficiary->full_name . " vient de recevoir le pot total pour le cycle " . $cycleNumber . " !"
            );

            // Cycle suivant
            if ($cycleNumber < $group->total_cycles) {
                $group->increment('current_cycle');
                $group->refresh();
                $nextDate = Carbon::parse($group->next_due_date);
                if ($group->frequency === 'weekly') $nextDate->addWeek();
                elseif ($group->frequency === 'biweekly') $nextDate->addWeeks(2);
                elseif ($group->frequency === 'monthly') $nextDate->addMonth();
                
                $group->update(['next_due_date' => $nextDate]);
                $this->cycles->ensureContributionsForCycle($group->fresh(), $group->current_cycle, $nextDate);
            } else {
                // === FIN DE LA TONTINE ===
                $group->update(['status' => 'completed']);

                // Suppression de la messagerie après la fin (Confidentialité)
                $group->messages()->delete();
                Log::info("Messagerie du groupe {$group->id} nettoyée après clôture.");

                // 1. Redistribution du reliquat du fonds de garantie (Cashback)
                if ($group->insurance_fund > 0) {
                    $members = $group->members()->where('status', 'active')->get();
                    $refundPerMember = $group->insurance_fund / $members->count();
                    
                    foreach ($members as $member) {
                        if ($member->user && $member->user->email) {
                            $content = "La tontine '" . $group->name . "' est maintenant terminée !\n\n" .
                                       "À l'issue de ce cycle, vous recevez un remboursement du reliquat du fonds de garantie de " . (int)$refundPerMember . " FCFA.\n\n" .
                                       "Merci de votre fidélité sur TontineChain.";
                            
                            Mail::to($member->user->email)->send(new TontineNotificationMail(
                                "Clôture de Tontine & Remboursement : " . $group->name,
                                $content
                            ));
                        }
                    }
                    $group->update(['insurance_fund' => 0]);
                }

                // 2. Bonus de Score Final pour les bons élèves
                $goodMembers = $group->members()->where('status', 'active')->get();
                foreach ($goodMembers as $member) {
                    $lateCount = $group->contributions()->where('user_id', $member->user_id)->where('is_late', true)->count();
                    if ($lateCount === 0) {
                        $member->user->increment('score_confiance', 25); // Bonus "Perfect Cycle"
                        if ($member->user->email) {
                            $content = "Bravo ! Vous avez obtenu un bonus de +25 points de score de confiance pour votre assiduité parfaite durant toute la tontine '" . $group->name . "'.\n\n" .
                                       "Votre profil est désormais plus attractif pour les futurs groupes.";
                            
                            Mail::to($member->user->email)->send(new TontineNotificationMail(
                                "Félicitations : Bonus de Confiance !",
                                $content
                            ));
                        }
                    }
                }
            }

            Log::info("Payout released for Group {$group->id}, Cycle {$cycleNumber}.");
        }
    }
}
