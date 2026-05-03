<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $apiKey;
    protected $baseUrl;
    protected $from;

    public function __construct()
    {
        $this->apiKey = env('INFOBIP_API_KEY');
        $this->baseUrl = env('INFOBIP_BASE_URL', 'https://api.infobip.com');
        $this->from = env('SMS_SENDER_NAME', 'TontineChain');
    }

    /**
     * Envoie une notification par SMS ET WhatsApp
     */
    public function notify($to, $message)
    {
        $normalizedTo = $this->normalizePhone($to);
        $this->sendSms($normalizedTo, $message);
        $this->sendWhatsApp($normalizedTo, $message);
    }

    /**
     * Envoie un SMS via Infobip
     */
    public function sendSms($to, $message)
    {
        if (!$this->apiKey || $this->apiKey === 'your-infobip-key') {
            Log::info("SMS Mock (Pas de clé API) pour $to : $message");
            return true;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "App {$this->apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->baseUrl}/sms/2/text/advanced", [
                'messages' => [
                    [
                        'from' => $this->from,
                        'destinations' => [['to' => $this->cleanPhoneNumber($to)]],
                        'text' => $message,
                    ]
                ]
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Erreur Infobip : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie un message WhatsApp via Infobip
     */
    public function sendWhatsApp($to, $message)
    {
        if (!$this->apiKey || $this->apiKey === 'your-infobip-key') {
            Log::info("WhatsApp Mock pour $to : $message");
            return true;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "App {$this->apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->baseUrl}/whatsapp/1/message/text", [
                'from' => env('WHATSAPP_SENDER_NUMBER', '447860088970'),
                'to' => $this->cleanPhoneNumber($to),
                'content' => [
                    'text' => $message
                ]
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Erreur WhatsApp Infobip : " . $e->getMessage());
            return false;
        }
    }

    public function normalizePhone($phone)
    {
        // Nettoyage des espaces et caractères spéciaux
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Si c'est un numéro local Bénin (8 chiffres) commençant par 0, on ajoute +229
        if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            return '+229' . substr($phone, 1);
        }

        // Si ça ne commence pas par +, on ajoute +
        return '+' . ltrim($phone, '+');
    }

    private function cleanPhoneNumber($phone)
    {
        // Infobip préfère le format international sans le +
        return ltrim($this->normalizePhone($phone), '+');
    }
}
