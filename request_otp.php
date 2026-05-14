<?php
$email = 'mourchidolawale@gmail.com';
$ch = curl_init('https://tonnine-benin-backend.onrender.com/api/v1/auth/request-otp');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => $email, 'locale' => 'fr']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP_CODE: $httpcode\n";
echo "RESPONSE: $response\n";
curl_close($ch);
