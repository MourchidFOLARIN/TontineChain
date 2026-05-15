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

echo "1. Récupération des cotisations du Cycle 2...\n";
$cycle2Contributions = [];
foreach ($tokens as $email => $token) {
    $res = callApi("$baseUrl/contributions/pending", 'GET', null, $token);
    if (!empty($res['body'])) {
        foreach ($res['body'] as $c) {
            if ($c['cycle_number'] == 2) {
                $cycle2Contributions[] = [
                    'id' => $c['id'],
                    'email' => $email,
                    'user_id' => $c['user_id']
                ];
                echo "   - $email a une cotisation Cycle 2 (ID: " . $c['id'] . ")\n";
            }
        }
    }
}

if (count($cycle2Contributions) < 3) {
    echo "ERREUR : Pas assez de cotisations Cycle 2 trouvées. Assurez-vous que le Cycle 1 est terminé.\n";
    exit;
}

echo "\n2. Simulation : Membre 1 et Membre 3 paient...\n";
$m1 = $cycle2Contributions[0];
$m3 = $cycle2Contributions[2];

foreach ([$m1, $m3] as $m) {
    // Initiation
    $resInit = callApi("$baseUrl/contributions/" . $m['id'] . "/pay", 'POST', [], $tokens[$m['email']]);
    $txId = $resInit['body']['transaction_id'];
    
    // Webhook validation
    $webhookData = ['event' => 'transaction.approved', 'entity' => ['id' => $txId, 'status' => 'approved']];
    $ch = curl_init("$baseUrl/webhooks/fedapay");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'X-Fedapay-Signature: wh_sandbox_STo2s4w_sl_SN1sDtoibvjhk']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    echo "   - " . $m['email'] . " a payé sa part du Cycle 2. ✅\n";
}

echo "\n3. Simulation : Le Membre 2 ne paie pas (On met sa date limite dans le passé)...\n";
$m2 = $cycle2Contributions[1];
echo "   - Membre fautif : " . $m2['email'] . " (ID Cotisation: " . $m2['id'] . ")\n";

// NOTE: Pour changer la date dans le passé sur Render, on ne peut pas le faire via API.
// On va devoir utiliser un script PHP sur le serveur pour simuler le passage du temps.
// Je vais créer un endpoint de debug temporaire ou utiliser une astuce.
?>
