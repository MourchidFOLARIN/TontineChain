<?php
$ch = curl_init('https://tonnine-benin-backend.onrender.com/api/v1/auth/request-otp');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => 'mourchidolawale@gmail.com', 'first_name' => 'Mourchid']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
echo curl_exec($ch);
