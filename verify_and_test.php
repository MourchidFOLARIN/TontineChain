<?php
$email = 'mourchidolawale@gmail.com';
$code = '194304';
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';

function callApi($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpcode, 'body' => json_decode($response, true)];
}

echo "--- Étape 2 : Vérification de l'OTP ---\n";
$res = callApi("$baseUrl/auth/verify-otp", 'POST', ['email' => $email, 'code' => $code]);
if ($res['code'] !== 200) {
    die("Erreur de vérification OTP : " . json_encode($res['body']) . "\n");
}
$token = $res['body']['access_token'];
echo "SUCCESS : Connecté ! Token reçu.\n\n";

echo "--- Étape 3 : Test /users/me (Profil) ---\n";
$res = callApi("$baseUrl/users/me", 'GET', null, $token);
echo "HTTP " . $res['code'] . " : " . ($res['code'] === 200 ? "Profil OK" : "Erreur") . "\n";
print_r($res['body']);

echo "\n--- Étape 4 : Test /groups (Liste des tontines) ---\n";
$res = callApi("$baseUrl/groups", 'GET', null, $token);
echo "HTTP " . $res['code'] . " : " . ($res['code'] === 200 ? "Groupes OK" : "Erreur") . "\n";
echo "Nombre de groupes : " . count($res['body']) . "\n";

echo "\n--- Étape 5 : Test /notifications ---\n";
$res = callApi("$baseUrl/notifications", 'GET', null, $token);
echo "HTTP " . $res['code'] . " : " . ($res['code'] === 200 ? "Notifications OK" : "Erreur") . "\n";
