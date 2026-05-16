<?php
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';
$token = '11|c4murvf6Bob4rCcDDe7WpA7JsvQ59fWst8jNSPSh06e60a0b';

echo "🌍 Connexion au serveur de production Render...\n";

// 1. Créer un groupe
echo "🏠 1. Création d'un groupe de démo...\n";
$ch = curl_init("$baseUrl/groups");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'name' => 'Démo Jury MIABE',
    'description' => 'Groupe de démonstration pour le paiement en ligne',
    'contribution_amount' => 1000,
    'frequency' => 'monthly',
    'max_members' => 5,
    'payout_method' => 'sequential',
    'start_date' => date('Y-m-d', strtotime('+1 day'))
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    "Authorization: Bearer $token"
]);
$res = curl_exec($ch);
$group = json_decode($res, true);

if (!isset($group['id'])) {
    die("❌ Erreur lors de la création du groupe : " . $res . "\n");
}
$groupId = $group['id'];
echo "✅ Groupe créé ID: $groupId\n";

// 2. Lancer la tontine (pour générer les cotisations)
echo "🚀 2. Lancement de la tontine...\n";
curl_setopt($ch, CURLOPT_URL, "$baseUrl/groups/$groupId/start");
$res = curl_exec($ch);
echo "✅ Tontine lancée.\n";

// 3. Récupérer la cotisation en attente
echo "💰 3. Récupération de la cotisation...\n";
curl_setopt($ch, CURLOPT_URL, "$baseUrl/contributions/pending");
curl_setopt($ch, CURLOPT_POST, false);
$res = curl_exec($ch);
$contributions = json_decode($res, true);

if (empty($contributions)) {
    die("❌ Aucune cotisation en attente trouvée sur la production.\n");
}
$contributionId = $contributions[0]['id'];
echo "✅ Cotisation trouvée ID: $contributionId\n";

// 4. Générer le lien de paiement
echo "💳 4. Génération du lien FedaPay...\n";
curl_setopt($ch, CURLOPT_URL, "$baseUrl/contributions/$contributionId/pay");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
$res = curl_exec($ch);
$payment = json_decode($res, true);

if (isset($payment['url'])) {
    echo "\n🔗 LIEN DE PAIEMENT PRODUCTION GÉNÉRÉ :\n" . $payment['url'] . "\n";
} else {
    echo "\n❌ Erreur finale :\n" . $res . "\n";
}

curl_close($ch);
