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

echo "1. Récupération des cotisations en attente...\n";
$allContributions = [];
foreach ($tokens as $email => $token) {
    $res = callApi("$baseUrl/contributions/pending", 'GET', null, $token);
    if (!empty($res['body'])) {
        foreach ($res['body'] as $c) {
            $allContributions[] = $c;
            echo "   - $email doit payer " . $c['amount_fcfa'] . " FCFA (ID: " . $c['id'] . ")\n";
        }
    }
}

echo "\n2. Simulation des paiements...\n";
foreach ($allContributions as $c) {
    // Étape A: Initier le paiement (pour avoir un transaction_id)
    $token = '';
    if (strpos($c['user_id'], 'mourchidolawale@gmail.com') !== false || true) {
         // On cherche le token correspondant
         foreach($tokens as $email => $t) {
             // Simplification: on essaie de trouver le token
         }
    }
    // Pour simplifier le test, on va utiliser le token du chef pour l'initiation (le contrôleur vérifie l'user_id)
    // Mais on a stocké les tokens par email. Cherchons le bon token.
    $userToken = $tokens['mourchidolawale@gmail.com']; // Default
    if (isset($tokens[$c['user_id']])) $userToken = $tokens[$c['user_id']];
    // En fait nos tokens sont indexés par EMAIL.
    // Retrouvons l'email via un mapping si besoin, ou on fait simple :
    $resInit = callApi("$baseUrl/contributions/" . $c['id'] . "/pay", 'POST', [], $userToken);
    
    if ($resInit['code'] !== 200) {
        // On essaie les autres tokens si celui-ci n'est pas le bon
        foreach($tokens as $t) {
            $resInit = callApi("$baseUrl/contributions/" . $c['id'] . "/pay", 'POST', [], $t);
            if ($resInit['code'] === 200) break;
        }
    }

    $transactionId = $resInit['body']['transaction_id'] ?? null;

    if (!$transactionId) {
        echo "   - ERREUR Initiation pour " . $c['id'] . "\n";
        continue;
    }

    echo "   - Transaction FedaPay créée : $transactionId\n";

    // Étape B: Envoyer le Webhook avec signature
    $webhookData = [
        'event' => 'transaction.approved',
        'entity' => [
            'id' => $transactionId,
            'status' => 'approved'
        ]
    ];
    $ch = curl_init("$baseUrl/webhooks/fedapay");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Fedapay-Signature: wh_sandbox_STo2s4w_sl_SN1sDtoibvjhk'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $resWebhook = json_decode($response, true);
    echo "   - Webhook envoyé : " . ($httpcode === 200 ? "SUCCESS" : "ERREUR ($httpcode)") . "\n";
    if ($httpcode === 401 && isset($resWebhook['debug'])) {
        echo "     DEBUG: " . json_encode($resWebhook['debug']) . "\n";
    }
}

echo "\n3. Vérification des Payouts (Ramassages)...\n";
// On vérifie avec le token du Chef
$chefToken = reset($tokens);
$res = callApi("$baseUrl/payouts", 'GET', null, $chefToken);
echo "Nombre de ramassages effectués : " . count($res['body']) . "\n";
if (!empty($res['body'])) {
    foreach ($res['body'] as $p) {
        echo "   - RAMASSAGE : " . $p['amount_fcfa'] . " FCFA payés à l'ID Utilisateur: " . $p['user_id'] . " (Status: " . $p['status'] . ")\n";
    }
} else {
    echo "   - Aucun ramassage détecté pour le moment.\n";
}
