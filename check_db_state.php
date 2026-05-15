<?php
$tokens = json_decode(file_get_contents('test_tokens.json'), true);
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';

function callApi($url, $method, $data, $token = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpcode, 'body' => json_decode($response, true)];
}

echo "--- DIAGNOSTIC DES GROUPES ---\n";
$chefToken = $tokens['mourchidolawale@gmail.com'];
$res = callApi("$baseUrl/groups", 'GET', null, $chefToken);
echo "Nombre de groupes : " . count($res['body'] ?? []) . "\n";
if (!empty($res['body'])) {
    foreach ($res['body'] as $g) {
        echo "   - GROUPE: " . $g['name'] . " | STATUS: " . $g['status'] . " | MEMBRES: " . $g['current_members'] . "/" . $g['max_members'] . " (ID: " . $g['id'] . ")\n";
    }
}

echo "\n--- DIAGNOSTIC DES INCIDENTS ---\n";
$resInc = callApi("$baseUrl/incidents", 'GET', null, $chefToken);
echo "Nombre d'incidents : " . count($resInc['body'] ?? []) . "\n";
if (!empty($resInc['body'])) {
    foreach ($resInc['body'] as $i) {
        echo "   🚩 " . $i['type'] . " | Utilisateur: " . $i['user_id'] . " | Impact: " . $i['score_impact'] . " (ID: " . $i['id'] . ")\n";
    }
}

echo "\n--- DIAGNOSTIC DES SCORES ---\n";
foreach ($tokens as $email => $token) {
    $resScore = callApi("$baseUrl/users/me/score", 'GET', null, $token);
    echo "   - $email : Score = " . ($resScore['body']['score'] ?? '100') . "\n";
}

