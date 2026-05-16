<?php
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';
$email = 'mourchidolawale@gmail.com';
$otp = '905767';

// 1. Vérification de l'OTP
echo "Vérification OTP...\n";
$ch = curl_init("$baseUrl/auth/verify-otp");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => $email, 'code' => $otp]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
$res = curl_exec($ch);
curl_close($ch);

$data = json_decode($res, true);

if (isset($data['token'])) {
    $token = $data['token'];
    echo "✅ Connecté avec succès ! Token reçu.\n\n";

    // 2. Test de YAO (Gemini)
    echo "Interrogation de YAO...\n";
    $ch2 = curl_init("$baseUrl/ai/chat");
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_POST, true);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode([
        'message' => 'Quel est mon score de confiance actuel et que penses-tu de mon dossier de tontine ?',
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
} else {
    echo "❌ Erreur de connexion :\n" . $res . "\n";
}
