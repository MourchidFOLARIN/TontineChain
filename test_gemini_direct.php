<?php
$geminiApiKey = 'AIzaSyBArrmstaOSc8zqQLeMmQYsgGczw1ehoW8';
$systemPrompt = "Tu es YAO, l'assistant IA.";
$message = "Test.";

$payload = [
    'contents' => [
        [
            'role' => 'user',
            'parts' => [
                ['text' => $systemPrompt . "\n\nVoici la question de l'utilisateur : " . $message]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 300,
    ]
];

$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-latest:generateContent?key={$geminiApiKey}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$res = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpcode\n";
echo "Response: $res\n";
