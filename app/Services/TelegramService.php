<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $token;
    protected $chatId;

    public function __construct()
    {
        $this->token = env('TELEGRAM_BOT_TOKEN');
        $this->chatId = env('TELEGRAM_CHAT_ID');
    }

    /**
     * Envoie un message via le Bot Telegram
     */
    public function sendMessage($message, $targetChatId = null)
    {
        $id = $targetChatId ?: $this->chatId;

        if (!$this->token || !$id) {
            Log::warning("Telegram Notification non envoyée : Token ou ChatID manquant.");
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
                'chat_id' => $id,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Erreur d'envoi Telegram : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie une alerte de paiement (Exemple)
     */
    public function sendPaymentAlert($userName, $amount, $groupName)
    {
        $msg = "💰 *Nouveau Paiement !*\n\n";
        $msg .= "👤 Membre : *{$userName}*\n";
        $msg .= "💵 Montant : *{$amount} FCFA*\n";
        $msg .= "🏢 Groupe : *{$groupName}*\n\n";
        $msg .= "🚀 La tontine avance !";

        return $this->sendMessage($msg);
    }
}
