<?php
$data = json_encode(['email' => 'mourchidolawale@gmail.com', 'locale' => 'fr']);
$ch = curl_init('https://tonnine-benin-backend.onrender.com/api/v1/auth/request-otp');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$response = curl_exec($ch);
$err = curl_error($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "HTTP $status\n";
if ($err) {
    echo "ERR: $err\n";
}
echo $response;
