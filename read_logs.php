<?php
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -20);
    echo implode("", $lastLines);
} else {
    echo "Fichier de log introuvable : $logFile\n";
    // Tentative de voir les logs système via une commande shell si possible
}
