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
        $this->sendSms($to, $message);
        $this->sendWhatsApp($to, $message);
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
            ])->post("{$this->baseUrl}/whatsapp/1/message/template", [
                'messages' => [
                    [
                        'from' => env('WHATSAPP_SENDER_NUMBER', '447860088970'),
                        'to' => $this->cleanPhoneNumber($to),
                        'content' => [
                            'templateName' => 'test_whatsapp_template_en',
                            'templateData' => [
                                'body' => [
                                    'placeholders' => ['Membre TontineChain']
                                ]
                            ],
                            'language' => 'en'
                        ]
                    ]
                ]
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Erreur WhatsApp Infobip : " . $e->getMessage());
            return false;
        }
    }

    private function cleanPhoneNumber($phone)
    {
        // Infobip préfère le format international sans le +
        return str_replace('+', '', $phone);
    }
}
