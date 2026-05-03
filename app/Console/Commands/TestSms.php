<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SmsService;

class TestSms extends Command
{
    protected $signature = 'sms:test {phone}';
    protected $description = 'Test Infobip SMS sending';

    public function handle(SmsService $sms)
    {
        $phone = $this->argument('phone');
        $this->info("Tentative d'envoi de SMS à $phone...");

        $message = "Test TontineChain : Votre connexion Infobip fonctionne ! 🚀";
        
        $result = $sms->sendSms($phone, $message);

        if ($result) {
            $this->info("✅ SMS envoyé avec succès !");
        } else {
            $this->error("❌ Échec de l'envoi du SMS. Vérifiez vos clés et votre crédit.");
        }
    }
}
