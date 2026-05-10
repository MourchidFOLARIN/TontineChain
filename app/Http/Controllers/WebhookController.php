<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Payout;
use App\Services\BlockchainService;
use App\Services\SmsService;
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

    public function __construct(BlockchainService $blockchain, SmsService $sms, TelegramService $telegram)
    {
        $this->blockchain = $blockchain;
        $this->sms = $sms;
        $this->telegram = $telegram;
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

        if ($webhookSecret) {
            if ($providedSecret !== $webhookSecret) {
                Log::warning("Tentative de Webhook frauduleuse détectée de l'IP: " . $request->ip());
                return response()->json(['error' => 'Unauthorized Signature'], 401);
            }
        }

        $event = $request->input('event');
        $data = $request->input('entity');

        Log::info("Webhook FedaPay received: $event");

        if ($event === 'transaction.approved') {
            return $this->processConfirmedPayment($data);
        }

        return response()->json(['status' => 'ignored']);
    }

    protected function processConfirmedPayment($data)
    {
        return DB::transaction(function () use ($data) {
            $contribution = Contribution::where('fedapay_transaction_id', $data['id'])->first();

            if (!$contribution) {
                Log::warning("Transaction {$data['id']} non trouvée.");
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
            
            // Logique de sélection du bénéficiaire (Séquentiel ou Aléatoire)
            $nextBeneficiary = $group->members()
                ->where('has_received', false)
                ->orderBy('position', 'asc')
                ->first();
            $payoutUserId = $nextBeneficiary->user_id;

            $discount = 0;
            $insuranceAmount = ($totalCycleAmount * $group->insurance_percent) / 100;
            $payoutAmount = $totalCycleAmount - $insuranceAmount - $discount;

            Payout::create([
                'group_id' => $group->id,
                'beneficiary_id' => $payoutUserId,
                'cycle_number' => $cycleNumber,
                'amount_fcfa' => $payoutAmount,
                'total_amount_fcfa' => $totalCycleAmount,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            GroupMember::where('group_id', $group->id)
                ->where('user_id', $payoutUserId)
                ->update(['has_received' => true]);

            // Blockchain
            $txHash = $this->blockchain->releasePayout($group->contract_address, $cycleNumber);
            
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
                $nextDate = Carbon::parse($group->next_due_date);
                if ($group->frequency === 'weekly') $nextDate->addWeek();
                elseif ($group->frequency === 'biweekly') $nextDate->addWeeks(2);
                elseif ($group->frequency === 'monthly') $nextDate->addMonth();
                
                $group->update(['next_due_date' => $nextDate]);
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
