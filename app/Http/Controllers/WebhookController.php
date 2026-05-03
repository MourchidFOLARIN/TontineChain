<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Payout;
use App\Services\BlockchainService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class WebhookController extends Controller
{
    protected $blockchain;
    protected $sms;

    public function __construct(BlockchainService $blockchain, SmsService $sms)
    {
        $this->blockchain = $blockchain;
        $this->sms = $sms;
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

        if ($webhookSecret && $webhookSecret !== 'your-fedapay-webhook-secret') {
            if ($providedSecret !== $webhookSecret) {
                Log::warning("Tentative de Webhook invalide détectée.");
                return response()->json(['error' => 'Unauthorized'], 401);
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

            // Envoi SMS + WhatsApp de confirmation
            $this->sms->notify($user->phone, "Paiement de {$contribution->amount_fcfa} FCFA confirmé pour le groupe {$group->name}. Merci !");

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
            
            // Logique Enchères
            $winningBid = null;
            $payoutUserId = null;

            if ($group->payout_method === 'bidding') {
                $winningBid = $group->bids()
                    ->where('cycle_number', $cycleNumber)
                    ->orderByDesc('discount_amount')
                    ->first();
                
                if ($winningBid) {
                    $winningBid->update(['status' => 'won']);
                    $payoutUserId = $winningBid->user_id;
                    
                    $group->bids()
                        ->where('cycle_number', $cycleNumber)
                        ->where('id', '!=', $winningBid->id)
                        ->update(['status' => 'lost']);
                }
            }

            if (!$payoutUserId) {
                $nextBeneficiary = $group->members()
                    ->where('has_received', false)
                    ->orderBy('position', 'asc')
                    ->first();
                $payoutUserId = $nextBeneficiary->user_id;
            }

            $discount = $winningBid ? $winningBid->discount_amount : 0;
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
            
            // Notification au bénéficiaire
            $beneficiary = GroupMember::where('group_id', $group->id)->where('user_id', $payoutUserId)->first()->user;
            $this->sms->notify($beneficiary->phone, "Félicitations ! Votre ramassage de {$payoutAmount} FCFA a été libéré sur votre compte Mobile Money.");

            // Cycle suivant
            if ($cycleNumber < $group->total_cycles) {
                $group->increment('current_cycle');
                $nextDate = Carbon::parse($group->next_due_date);
                if ($group->frequency === 'weekly') $nextDate->addWeek();
                elseif ($group->frequency === 'biweekly') $nextDate->addWeeks(2);
                elseif ($group->frequency === 'monthly') $nextDate->addMonth();
                
                $group->update(['next_due_date' => $nextDate]);
            } else {
                $group->update(['status' => 'completed']);
            }

            Log::info("Payout released for Group {$group->id}, Cycle {$cycleNumber}.");
        }
    }
}
