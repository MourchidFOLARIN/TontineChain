<?php
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';
$token = '11|c4murvf6Bob4rCcDDe7WpA7JsvQ59fWst8jNSPSh06e60a0b';

$tests = [
    ['message' => 'Quel est mon score de confiance actuel et que penses-tu de mon dossier ?', 'locale' => 'fr',  'label' => '1. Score de confiance (FR)'],
    ['message' => 'Bilan akwe nabi ? Mto FCFA mi wezun ?',                                    'locale' => 'fon', 'label' => '2. Bilan en Fon'],
    ['message' => 'Comment fonctionne la blockchain dans TontineChain ?',                     'locale' => 'fr',  'label' => '3. Blockchain (FR)'],
    ['message' => 'Quand est ma prochaine echeance de cotisation ?',                          'locale' => 'fr',  'label' => '4. Prochaine echeance (FR)'],
    ['message' => 'Explique-moi le systeme d encheres',                                       'locale' => 'fr',  'label' => '5. Systeme d encheres (FR)'],
    ['message' => 'Hello YAO, comment tu vas ?',                                              'locale' => 'fr',  'label' => '6. Salutation / hors-sujet'],
];

foreach ($tests as $t) {
    echo "\n===================================\n";
    echo "Test : " . $t['label'] . "\n";
    echo "===================================\n";
    echo "Question : " . $t['message'] . "\n";
    echo "Langue   : " . $t['locale'] . "\n";
    echo "-----------------------------------\n";

    $ch = curl_init("$baseUrl/ai/chat");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'message' => $t['message'],
        'locale'  => $t['locale']
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        "Authorization: Bearer $token"
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $res     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    echo "HTTP : $httpCode\n";

    if ($curlErr) {
        echo "CURL Erreur : $curlErr\n";
        continue;
    }

    $data = json_decode($res, true);
    if (isset($data['message'])) {
        echo "YAO : " . $data['message'] . "\n";
    } elseif (isset($data['error'])) {
        echo "Erreur API : " . $data['error'] . "\n";
    } elseif (isset($data['message'])) {
        echo "Message : " . $data['message'] . "\n";
    } else {
        echo "Reponse brute : $res\n";
    }

    sleep(2);
}

echo "\n============================\n";
echo "Tests termines.\n";
echo "============================\n";
