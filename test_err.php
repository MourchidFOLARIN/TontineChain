<?php
$data = json_encode(['email' => 'mourchidolawale@gmail.com', 'locale' => 'fr']);
$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\nAccept: application/json\r\n",
        'content' => $data,
        'ignore_errors' => true
    ]
];
$context = stream_context_create($options);
$result = file_get_contents('https://tonnine-benin-backend.onrender.com/api/v1/auth/request-otp', false, $context);
echo "\nRESPONSE:\n" . $result . "\n";
