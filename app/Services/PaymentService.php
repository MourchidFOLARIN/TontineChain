<?php

namespace App\Services;

use App\Models\Contribution;
use App\Models\User;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct()
    {
        FedaPay::setApiKey(env('FEDAPAY_API_KEY'));
        FedaPay::setEnvironment(env('FEDAPAY_ENV', 'sandbox'));
    }

    public function initiatePayment(Contribution $contribution, User $user)
    {
        Log::info("Initiating FedaPay SDK payment for contribution: {$contribution->id}");

        try {
            $customer = [
                'firstname' => $user->first_name ?: 'Membre',
                'lastname' => $user->last_name ?: 'TontiGo',
                'email' => $user->email ?: 'user_'.$user->id.'@tontigo.app',
            ];

            if (! empty($user->phone)) {
                $customer['phone_number'] = [
                    'number' => $user->phone,
                    'country' => 'BJ',
                ];
            }

            $transaction = Transaction::create([
                'description' => "Cotisation TontiGo - Cycle {$contribution->cycle_number}",
                'amount' => (int) $contribution->amount_fcfa,
                'currency' => ['iso' => 'XOF'],
                'callback_url' => env('APP_URL') . '/api/v1/webhooks/fedapay',
                'customer' => $customer,
            ]);

            $token = $transaction->generateToken();

            $contribution->update([
                'status' => 'processing',
                'fedapay_transaction_id' => (string) $transaction->id,
                'mobile_money_provider' => 'fedapay'
            ]);

            return [
                'payment_url' => $token->url,
                'transaction_id' => $transaction->id,
            ];

        } catch (\Exception $e) {
            Log::error("FedaPay SDK Error: " . $e->getMessage());
            throw new \Exception("Erreur FedaPay : " . $e->getMessage());
        }
    }
}
