<?php
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';
$token = '11|c4murvf6Bob4rCcDDe7WpA7JsvQ59fWst8jNSPSh06e60a0b';

echo "Interrogation de YAO (Production)...\n";
$ch2 = curl_init("$baseUrl/ai/chat");
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode([
    'message' => 'Quel est mon score de confiance actuel et que penses-tu de mon dossier ?',
    'locale' => 'fr'
]));
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    "Authorization: Bearer $token"
]);

$res2 = curl_exec($ch2);
curl_close($ch2);

$aiData = json_decode($res2, true);
if (isset($aiData['message'])) {
    echo "\n🤖 Réponse de YAO :\n" . $aiData['message'] . "\n";
} else {
    echo "\n❌ Erreur YAO :\n" . $res2 . "\n";
}
