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

echo "1. Récupération des cotisations du Cycle 2 (Même groupe)...\n";
$chefToken = $tokens['mourchidolawale@gmail.com'];
$resPending = callApi("$baseUrl/contributions/pending", 'GET', null, $chefToken);

$targetGroupId = null;
$cycle2Contributions = [];

if (!empty($resPending['body'])) {
    foreach ($resPending['body'] as $c) {
        if ($c['cycle_number'] == 2) {
            $targetGroupId = $c['group_id'];
            break;
        }
    }
}

if (!$targetGroupId) {
    echo "ERREUR : Aucun cycle 2 trouvé pour le chef. Terminez un cycle 1 d'abord.\n";
    exit;
}

echo "   - Groupe cible : $targetGroupId\n";

// Récupérer les cotisations de ce groupe pour les 3 membres
$emails = array_keys($tokens);
foreach ($emails as $email) {
    $res = callApi("$baseUrl/contributions/pending", 'GET', null, $tokens[$email]);
    if (!empty($res['body'])) {
        foreach ($res['body'] as $c) {
            if ($c['group_id'] == $targetGroupId && $c['cycle_number'] == 2) {
                $cycle2Contributions[] = [
                    'id' => $c['id'],
                    'email' => $email,
                    'user_id' => $c['user_id']
                ];
                echo "   - $email ajouté (ID Cotisation: " . $c['id'] . ")\n";
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

echo "\n3. Simulation : Le Membre 2 ne paie pas (On FORCE l'incident)...\n";
$m2 = $cycle2Contributions[1];
echo "   - Membre fautif : " . $m2['email'] . " (ID Cotisation: " . $m2['id'] . ")\n";

// A: On force l'incident et la baisse de score
$resForce = callApi("$baseUrl/debug/force-incident/" . $m2['id'], 'GET', null, $tokens['mourchidolawale@gmail.com']);
echo "   - Statut : " . ($resForce['body']['status'] ?? 'Erreur') . "\n";
echo "   - NOUVEAU SCORE (Serveur) : " . ($resForce['body']['new_score'] ?? 'Inconnu') . "\n";

echo "\n4. Vérification des conséquences...\n";
// On vérifie les incidents
$resIncidents = callApi("$baseUrl/incidents", 'GET', null, $tokens['mourchidolawale@gmail.com']);
echo "   - Nombre d'incidents détectés : " . count($resIncidents['body'] ?? []) . "\n";
if (!empty($resIncidents['body'])) {
    foreach ($resIncidents['body'] as $inc) {
        if ($inc['user_id'] == $m2['user_id'] || true) {
            echo "   🚩 INCIDENT : " . ($inc['description'] ?? 'Sans description') . " (Impact: " . ($inc['score_impact'] ?? '0') . " points)\n";
        }
    }
}


?>
