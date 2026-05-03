<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SmsService;

class TestWhatsApp extends Command
{
    protected $signature = 'whatsapp:test {phone}';
    protected $description = 'Test Infobip WhatsApp sending';

    public function handle(SmsService $sms)
    {
        $phone = $this->argument('phone');
        $this->info("Tentative d'envoi de message WhatsApp à $phone...");

        $message = "Test TontineChain : Votre connexion WhatsApp Infobip fonctionne ! 🟢🚀";
        
        $result = $sms->sendWhatsApp($phone, $message);

        if ($result) {
            $this->info("✅ Message WhatsApp envoyé avec succès !");
            $this->line("Note : Si vous ne recevez rien, vérifiez que vous avez activé le Sandbox WhatsApp sur votre panel Infobip.");
        } else {
            $this->error("❌ Échec de l'envoi WhatsApp.");
        }
    }
}
