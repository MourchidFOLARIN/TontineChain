<?php

namespace App\Services;

use App\Models\Contribution;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('FEDAPAY_API_KEY');
        $this->baseUrl = env('FEDAPAY_ENV') === 'live' 
            ? 'https://api.fedapay.com/v1' 
            : 'https://sandbox-api.fedapay.com/v1';
    }

    public function initiatePayment(Contribution $contribution, User $user)
    {
        Log::info("Initiating FedaPay payment for contribution: {$contribution->id}");

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post("{$this->baseUrl}/transactions", [
            'description' => "Cotisation TontineChain - Cycle {$contribution->cycle_number}",
            'amount' => (int) $contribution->amount_fcfa,
            'currency' => ['iso' => 'XOF'],
            'callback_url' => env('APP_URL') . '/api/v1/webhooks/fedapay',
            'customer' => [
                'firstname' => explode(' ', $user->full_name)[0] ?? 'Membre',
                'lastname' => explode(' ', $user->full_name)[1] ?? 'Tontine',
                'phone_number' => [
                    'number' => $user->phone,
                    'country' => 'BJ'
                ]
            ],
            'metadata' => [
                'contribution_id' => $contribution->id,
                'group_id' => $contribution->group_id,
                'user_id' => $user->id,
                'cycle_number' => $contribution->cycle_number
            ]
        ]);

        if ($response->failed()) {
            Log::error("FedaPay initiation failed: " . $response->body());
            throw new \Exception("Erreur lors de l'initiation du paiement Mobile Money.");
        }

        $transaction = $response->json()['transaction'];

        $contribution->update([
            'status' => 'processing',
            'mobile_money_ref' => (string) $transaction['id'],
            'mobile_money_provider' => 'fedapay'
        ]);

        return [
            'payment_url' => $transaction['links']['url'] ?? '', // FedaPay returns the checkout URL
            'transaction_id' => $transaction['id'],
        ];
    }
}
