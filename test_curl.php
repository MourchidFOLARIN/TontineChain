<?php
$ch = curl_init('https://tonnine-benin-backend.onrender.com/api/v1/auth/request-otp');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => 'mourchid200523@gmail.com', 'locale' => 'fr']));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
// Ignorer la vérification SSL locale si nécessaire
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if(curl_errno($ch)){
    echo 'Curl error: ' . curl_error($ch) . "\n";
}
echo "HTTP_CODE: $httpcode\n";
echo "RESPONSE_BODY:\n$response\n";
curl_close($ch);
