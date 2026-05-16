<?php
$tokens = json_decode(file_get_contents('test_tokens.json'), true);
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';
$token = current($tokens);

$ch = curl_init("$baseUrl/groups");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$groups = json_decode(curl_exec($ch), true);
curl_close($ch);

echo "--- ÉTAT DU GROUPE ---\n";
if (!$groups) {
    echo "Aucun groupe trouvé.\n";
} else {
    foreach ($groups as $g) {
        echo "Groupe: " . $g['name'] . " | Statut: " . $g['status'] . "\n";
    }
}
