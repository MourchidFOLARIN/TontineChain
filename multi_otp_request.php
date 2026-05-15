<?php
$emails = [
    'mourchidolawale@gmail.com',
    'mourchidolawale20053@gmail.com',
    'mourchid200523@gmail.com'
];

foreach ($emails as $email) {
    $ch = curl_init('https://tonnine-benin-backend.onrender.com/api/v1/auth/request-otp');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => $email, 'locale' => 'fr']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "EMAIL: $email | HTTP: $httpcode | RESPONSE: $response\n";
    curl_close($ch);
    usleep(500000); // 0.5s pause
}
