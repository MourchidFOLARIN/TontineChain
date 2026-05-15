<?php
$email = $argv[1] ?? '';
$code = $argv[2] ?? '';
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';

if (!$email || !$code) die("Usage: php verify_multi.php <email> <code>\n");

$ch = curl_init("$baseUrl/auth/verify-otp");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => $email, 'code' => $code]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
if ($httpcode === 200 && isset($data['access_token'])) {
    $tokens = file_exists('test_tokens.json') ? json_decode(file_get_contents('test_tokens.json'), true) : [];
    $tokens[$email] = $data['access_token'];
    file_put_contents('test_tokens.json', json_encode($tokens));
    echo "SUCCESS: $email est connecté.\n";
} else {
    echo "ERROR: " . $response . "\n";
}
