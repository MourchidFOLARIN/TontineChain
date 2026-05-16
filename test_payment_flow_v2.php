<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use App\Models\Group;
use App\Models\Contribution;
use Illuminate\Support\Str;

$email = 'mourchidolawale@gmail.com';
$user = User::where('email', $email)->first();

if (!$user) {
    echo "👤 Création de l'utilisateur Mourchid...\n";
    $user = User::create([
        'email' => $email,
        'full_name' => 'Mour FOLA',
        'phone' => '+229 0142100156',
        'is_active' => true,
        'wallet_address' => '0x' . Str::random(40),
        'score_confiance' => 100
    ]);
}

// 1. Créer le groupe
echo "🏠 Création du groupe de test...\n";
$group = Group::create([
    'name' => 'Groupe Test Démo',
    'description' => 'Test de paiement FedaPay',
    'creator_id' => $user->id,
    'contribution_amount' => 5000,
    'frequency' => 'monthly',
    'max_members' => 10,
    'status' => 'active',
    'currency' => 'XOF'
]);

// 2. Créer la cotisation
echo "💰 Création de la cotisation...\n";
$contribution = Contribution::create([
    'user_id' => $user->id,
    'group_id' => $group->id,
    'amount_fcfa' => 5000,
    'cycle_number' => 1,
    'status' => 'pending',
    'due_date' => now()->addDays(7)
]);

echo "✅ Cotisation créée avec l'ID : " . $contribution->id . "\n";
echo "🚀 Génération du lien de paiement (Production)...\n";

// 3. Obtenir un token frais (on simule car on ne peut pas faire d'OTP ici)
// On va appeler l'endpoint de production directement.
$token = '11|c4murvf6Bob4rCcDDe7WpA7JsvQ59fWst8jNSPSh06e60a0b';
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';

// Note: On utilise l'ID de la cotisation qu'on vient de créer localement, 
// mais comme on appelle le serveur de production, ça risque de ne pas marcher 
// si l'ID n'existe pas là-bas. 
// Je vais plutôt appeler l'API LOCALE pour te montrer que ça marche chez toi !

$localUrl = 'http://localhost:8000/api/v1'; // Assure-toi que ton serveur local tourne !
// Sinon, je vais utiliser le service directement sans passer par HTTP.

try {
    $service = app(\App\Services\PaymentService::class);
    $payment = $service->initiatePayment($contribution, $user);
    echo "\n🔗 LIEN FEDAPAY GÉNÉRÉ (Local) :\n" . $payment['payment_url'] . "\n";
} catch (\Exception $e) {
    echo "\n❌ ERREUR :\n" . $e->getMessage() . "\n";
}
