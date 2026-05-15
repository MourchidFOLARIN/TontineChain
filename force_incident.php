<?php
use App\Models\Contribution;
use App\Models\User;
use App\Models\Incident;
use Carbon\Carbon;

// Ce script sera exécuté via une route de debug pour être sûr que la logique tourne sur le serveur
$id = $_GET['id'] ?? null;
if (!$id) die("ID manquant");

$contrib = Contribution::find($id);
if (!$contrib) die("Cotisation introuvable");

echo "Traitement manuel du retard pour : " . $contrib->user->email . "\n";

$contrib->update([
    'is_late' => true,
    'late_days' => 2,
    'status' => 'late'
]);

$scoreImpact = -15; // On tape fort pour la démo
$contrib->user->increment('score_confiance', $scoreImpact);

Incident::create([
    'group_id' => $contrib->group_id,
    'user_id' => $contrib->user_id,
    'type' => 'late_payment',
    'description' => "SIMULATION: Retard critique Cycle " . $contrib->cycle_number,
    'cycle_number' => $contrib->cycle_number,
    'score_impact' => $scoreImpact
]);

echo "SCORE MIS À JOUR : " . $contrib->user->score_confiance . "\n";
echo "INCIDENT CRÉÉ.";
