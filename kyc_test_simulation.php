<?php
$tokens = json_decode(file_get_contents('test_tokens.json'), true);
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';
$token = $tokens['mourchidolawale20053@gmail.com'];
$filePath = __DIR__ . '/dummy_id.png';

echo "1. Simulation de l'upload de la pièce d'identité...\n";

if (!file_exists($filePath)) {
    die("ERREUR : Image dummy_id.png introuvable.\n");
}

$ch = curl_init("$baseUrl/users/me/kyc");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

$cFile = new CURLFile($filePath, 'image/png', 'document.png');
curl_setopt($ch, CURLOPT_POSTFIELDS, ['document' => $cFile]);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$res = json_decode($response, true);

echo "   - Status HTTP : $httpcode\n";
if ($httpcode == 200) {
    echo "   - Message : " . $res['message'] . " ✅\n";
    echo "   - Statut KYC : " . $res['status'] . "\n";
    echo "   - Analyse OCR (YAO) : " . ($res['demo_notice']['message'] ?? 'N/A') . " 🤖✨\n";
} else {
    echo "   - Erreur : " . ($res['error'] ?? $response) . " ❌\n";
}

echo "\n2. Vérification du profil mis à jour...\n";
$ch = curl_init("$baseUrl/users/me");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Accept: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$profileRes = json_decode(curl_exec($ch), true);
curl_close($ch);

echo "   - Nouveau statut KYC sur le profil : " . ($profileRes['kyc_status'] ?? 'N/A') . "\n";
if (!empty($profileRes['id_card_path'])) {
    echo "   - Image stockée sur le serveur : " . $profileRes['id_card_path'] . " 📂\n";
}
