<?php
$baseUrl = 'https://tonnine-benin-backend.onrender.com/api/v1';
$token = '11|c4murvf6Bob4rCcDDe7WpA7JsvQ59fWst8jNSPSh06e60a0b'; // Token de test pour Mourchid

$questions = [
    "Salut YAO ! Comment fonctionne le système d'enchères (bidding) sur TontineChain si je veux ramasser l'argent en avance ?",
    "Peux-tu m'expliquer très brièvement pourquoi vous utilisez la blockchain Polygon pour sécuriser l'argent ?",
    "Que se passe-t-il si je ne paie pas ma cotisation à l'heure ? Est-ce que mon score de 100/100 va baisser ?"
];

echo "🚀 Lancement de la batterie de tests sur YAO (Gemini 2.5 - Production)...\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($questions as $index => $question) {
    echo "🙋‍♂️ QUESTION " . ($index + 1) . " :\n";
    echo '"' . $question . '"' . "\n\n";

    $ch = curl_init("$baseUrl/ai/chat");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'message' => $question,
        'locale' => 'fr'
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        "Authorization: Bearer $token"
    ]);

    $res = curl_exec($ch);
    curl_close($ch);

    $aiData = json_decode($res, true);
    
    echo "🤖 RÉPONSE DE YAO :\n";
    if (isset($aiData['message'])) {
        echo $aiData['message'] . "\n";
    } else {
        echo "❌ Erreur : " . $res . "\n";
    }
    echo str_repeat("-", 80) . "\n\n";
    
    // Pause de 2 secondes entre les requêtes pour ne pas surcharger l'API
    sleep(2);
}

echo "✅ Batterie de tests terminée !\n";
