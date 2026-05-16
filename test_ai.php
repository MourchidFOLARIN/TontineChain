<?php

// Script pour tester l'API de production TontineChain
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';

echo "1. Inscription d'un utilisateur de test...\n";
$ch = curl_init("$baseUrl/auth/register");
$payload = json_encode([
    'first_name' => 'Test',
    'last_name' => 'Gemini',
    'email' => 'test_gemini_' . time() . '@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'country' => 'BJ'
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

if ($httpcode >= 200 && $httpcode < 300 && isset($data['token'])) {
    $token = $data['token'];
    echo "✅ Utilisateur créé. Token reçu.\n\n";

    echo "2. Envoi d'un message à l'IA YAO (Gemini)...\n";
    $ch2 = curl_init("$baseUrl/ai/chat");
    $payload2 = json_encode([
        'message' => 'Que penses-tu de mon historique de paiement ?',
        'locale' => 'fr'
    ]);

    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_POST, true);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, $payload2);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        "Authorization: Bearer $token"
    ]);

    $response2 = curl_exec($ch2);
    $httpcode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);

    echo "Status Code : $httpcode2\n";
    echo "Réponse de YAO :\n";
    $data2 = json_decode($response2, true);
    if (isset($data2['message'])) {
        echo "🤖 " . $data2['message'] . "\n";
    } else {
        echo "Erreur ou Fallback : " . $response2 . "\n";
    }
} else {
    echo "❌ Échec de l'inscription. Code: $httpcode\n";
    echo $response . "\n";
}
