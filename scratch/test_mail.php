<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

try {
    echo "Tentative d'envoi d'un email de test vers mourchidolawale@gmail.com...\n";
    
    Mail::raw("Ceci est un test local de TontineChain. Si vous lisez ceci, la configuration Gmail est correcte ! 🚀", function ($message) {
        $message->to('mourchidolawale@gmail.com')
                ->subject('Test Local TontineChain');
    });

    echo "SUCCÈS : L'email a été envoyé sans erreur ! ✅\n";
} catch (\Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
}
