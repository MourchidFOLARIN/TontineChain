<?php
$tokens = json_decode(file_get_contents('test_tokens.json'), true);
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';

// On récupère un groupe actif
$tokenM1 = current($tokens);
$ch = curl_init("$baseUrl/groups");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenM1]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$groups = json_decode(curl_exec($ch), true);
curl_close($ch);

$activeGroups = array_filter($groups, function($g) { return $g['status'] === 'active'; });
$group = reset($activeGroups) ?: null;
if (!$group) die("Aucun groupe ACTIF trouvé.\n");

$groupId = $group['id'];
echo "--- SIMULATION DE CHAT (Groupe: " . $group['name'] . ") ---\n\n";

$messages = [
    ['user' => 'mourchidolawale@gmail.com', 'content' => "Salut les amis ! Prêt pour cette nouvelle aventure ? 🚀"],
    ['user' => 'mourchidolawale20053@gmail.com', 'content' => "Bonjour tout le monde ! Oui, j'ai hâte de commencer."],
    ['user' => 'mourchid200523@gmail.com', 'content' => "C'est parti ! On va faire de grandes choses ensemble. 💪"]
];

foreach ($messages as $m) {
    $token = $tokens[$m['user']];
    echo "👤 Envoi par " . $m['user'] . "... ";
    
    $ch = curl_init("$baseUrl/groups/$groupId/messages");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['content' => $m['content']]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);
    
    if (isset($res['id'])) {
        echo "✅\n";
    } else {
        echo "❌ (" . json_encode($res) . ")\n";
    }
    usleep(500000);
}

echo "\n--- RÉCUPÉRATION DE L'HISTORIQUE ---\n";
$ch = curl_init("$baseUrl/groups/$groupId/messages");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenM1]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$history = json_decode(curl_exec($ch), true);
curl_close($ch);

foreach ($history as $msg) {
    $sender = $msg['is_system'] ? "📢 SYSTÈME" : "👤 " . ($msg['user']['full_name'] ?? 'Utilisateur');
    echo "$sender : " . $msg['content'] . "\n";
}
