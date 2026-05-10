<?php

namespace App\Services;

use App\Models\Group;
use App\Models\User;
use App\Models\Payout;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayoutService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('FEDAPAY_SECRET_KEY');
        $this->baseUrl = env('FEDAPAY_BASE_URL', 'https://sandbox.fedapay.com/api/v1');
    }

    /**
     * Envoie la cagnotte au gagnant via FedaPay (Simulation)
     */
    public function sendCagnotte(Group $group, User $winner, $amount)
    {
        Log::info("Initiation du reversement de $amount FCFA pour {$winner->full_name}");

        // Simulation de l'appel API FedaPay Payout
        if (!$this->apiKey || str_contains($this->apiKey, 'sk_test_')) {
            Log::info("FEDAPAY PAYOUT MOCK : Envoi de $amount FCFA au numéro {$winner->phone}");
            
            return Payout::create([
                'group_id' => $group->id,
                'beneficiary_id' => $winner->id,
                'cycle_number' => $group->current_cycle,
                'amount_fcfa' => $amount,
                'status' => 'completed',
                'payout_method' => 'mobile_money',
                'external_reference' => 'FED_PAY_' . uniqid(),
            ]);
        }

        // Ici viendrait l'intégration réelle avec FedaPay\Payout
        // Pour la démo, on s'arrête au log/mock sécurisé.
        return null;
    }
}
