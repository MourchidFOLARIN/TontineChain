<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use App\Models\Group;
use App\Models\Contribution;

$email = 'mourchidolawale@gmail.com';
$user = User::where('email', $email)->first();

if (!$user) {
    die("Utilisateur non trouvé.\n");
}

// 1. Créer le groupe
$group = Group::create([
    'name' => 'Groupe Test Démo',
    'description' => 'Test de paiement FedaPay',
    'creator_id' => $user->id,
    'amount' => 5000,
    'frequency' => 'monthly',
    'max_members' => 10,
    'status' => 'active',
    'currency' => 'XOF'
]);

// 2. Créer la cotisation
$contribution = Contribution::create([
    'user_id' => $user->id,
    'group_id' => $group->id,
    'amount' => 5000,
    'cycle_number' => 1,
    'status' => 'pending',
    'due_date' => now()->addDays(7)
]);

echo "✅ Cotisation créée avec l'ID : " . $contribution->id . "\n";
echo "🚀 Génération du lien de paiement...\n";

// 3. Simuler l'appel à l'endpoint de paiement
$token = '11|c4murvf6Bob4rCcDDe7WpA7JsvQ59fWst8jNSPSh06e60a0b';
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';

// Pour le test, on va utiliser le PaymentService directement ou faire un curl sur Render
// Je vais faire un curl sur le serveur de production pour être sûr que tout est OK là-bas aussi.
$ch = curl_init("$baseUrl/contributions/{$contribution->id}/pay");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    "Authorization: Bearer $token"
]);

$res = curl_exec($ch);
curl_close($ch);

$data = json_decode($res, true);
if (isset($data['url'])) {
    echo "\n🔗 LIEN FEDAPAY GÉNÉRÉ :\n" . $data['url'] . "\n";
} else {
    echo "\n❌ ERREUR :\n" . $res . "\n";
}
