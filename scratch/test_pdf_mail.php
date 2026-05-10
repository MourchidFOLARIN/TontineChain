<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Group;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\TontineContractMail;
use Illuminate\Support\Str;

try {
    // On prend un groupe ou on en "mocke" un rapidement s'il n'y en a pas
    $group = Group::with('members.user', 'creator')->first();
    
    if (!$group) {
        echo "Aucun groupe en base, tentative de création d'un groupe fictif...\n";
        $user = \App\Models\User::first() ?? \App\Models\User::create([
            'phone' => '+22997000000',
            'full_name' => 'John Doe Test',
        ]);

        $group = Group::create([
            'name' => 'Tontine Hackathon Test',
            'creator_id' => $user->id,
            'contribution_amount' => 50000,
            'max_members' => 5,
            'frequency' => 'monthly',
            'payout_method' => 'random',
            'start_date' => now(),
            'status' => 'active',
            'contract_address' => '0xTestPolygonContractAddress',
            'contract_tx_hash' => '0x1234567890abcdef1234567890abcdef',
        ]);
        
        \App\Models\GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'position' => 1,
            'status' => 'active',
        ]);
        $group->load('members.user', 'creator');
    }

    echo "Génération du PDF pour le groupe : " . $group->name . "...\n";
    $pdf = Pdf::loadView('pdf.tontine_contract', ['group' => $group]);
    $pdfContent = $pdf->output();

    echo "Envoi de l'email...\n";
    Mail::to('mourchidolawale@gmail.com')->send(new TontineContractMail($group, $pdfContent));

    echo "SUCCÈS : L'email avec le contrat PDF a été envoyé à mourchidolawale@gmail.com ! ✅\n";
} catch (\Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
