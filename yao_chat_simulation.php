<?php
$tokens = json_decode(file_get_contents('test_tokens.json'), true);
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';
$token = $tokens['mourchidolawale20053@gmail.com'];

function askYao($message, $token, $baseUrl) {
    $ch = curl_init("$baseUrl/ai/chat");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['message' => $message, 'locale' => 'fr']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$questions = [
    "Bonjour YAO, mè wi ?",
    "Quel est mon score de confiance ?",
    "Combien d'akwé j'ai déjà payé en tout ?",
    "C'est quoi la blockchain ?"
];

echo "--- DÉBUT DE LA CONVERSATION AVEC YAO --- \n\n";

foreach ($questions as $q) {
    echo "👤 Membre : $q\n";
    $res = askYao($q, $token, $baseUrl);
    echo "🤖 YAO : " . ($res['message'] ?? 'Erreur') . "\n";
    echo "------------------------------------------\n";
    sleep(1); // Pour simuler la réflexion de l'IA
}
