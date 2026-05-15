<?php
$tokens = json_decode(file_get_contents('test_tokens.json'), true);
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';

$chefToken = $tokens['mourchidolawale@gmail.com'];
$m2Token = $tokens['mourchidolawale20053@gmail.com'];
$m3Token = $tokens['mourchid200523@gmail.com'];

function callApi($url, $method, $data, $token) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $headers = ['Content-Type: application/json', 'Accept: application/json', 'Authorization: Bearer ' . $token];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpcode, 'body' => json_decode($response, true)];
}

echo "1. Chef crée le groupe...\n";
$groupData = [
    'name' => 'Tontine Hackathon 2026',
    'contribution_amount' => 10000,
    'max_members' => 3,
    'frequency' => 'weekly',
    'payout_method' => 'sequential',
    'start_date' => date('Y-m-d H:i:s', strtotime('+1 week'))
];
$res = callApi("$baseUrl/groups", 'POST', $groupData, $chefToken);
$groupId = $res['body']['id'];
echo "Groupe créé ID: $groupId\n";

echo "2. Chef invite Membre 2...\n";
callApi("$baseUrl/groups/$groupId/invite", 'POST', ['email' => 'mourchidolawale20053@gmail.com', 'phone' => '+22997000001'], $chefToken);

echo "3. Chef invite Membre 3...\n";
callApi("$baseUrl/groups/$groupId/invite", 'POST', ['email' => 'mourchid200523@gmail.com', 'phone' => '+22997000002'], $chefToken);

echo "4. Membre 2 rejoint le groupe...\n";
callApi("$baseUrl/groups/$groupId/join", 'POST', [], $m2Token);

echo "5. Membre 3 rejoint le groupe...\n";
callApi("$baseUrl/groups/$groupId/join", 'POST', [], $m3Token);

echo "Pause de 2 secondes pour la synchronisation...\n";
sleep(2);

echo "6. Chef démarre la tontine...\n";
$res = callApi("$baseUrl/groups/$groupId/start", 'POST', [], $chefToken);

echo "FIN DU TEST.\n";
print_r($res['body']);
