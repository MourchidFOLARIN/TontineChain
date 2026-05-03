<?php

namespace App\Services;

use App\Models\Group;

class RiskAnalysisService
{
    /**
     * Calcule l'indice de risque d'un groupe (0-100, plus c'est élevé, plus c'est risqué).
     */
    public function analyzeGroupRisk(Group $group): array
    {
        $members = $group->members()->with('user')->get();
        if ($members->isEmpty()) {
            return ['score' => 0, 'level' => 'Inconnu', 'factors' => []];
        }

        $totalScore = 0;
        $factors = [];
        $unverifiedCount = 0;

        foreach ($members as $member) {
            $totalScore += $member->user->score_confiance;
            if ($member->user->kyc_status !== 'verified') {
                $unverifiedCount++;
            }
        }

        $avgTrustScore = $totalScore / $members->count();
        
        // Calcul de la volatilité du groupe (Écart-type)
        $variance = 0;
        foreach ($members as $member) {
            $variance += pow($member->user->score_confiance - $avgTrustScore, 2);
        }
        $stdDev = sqrt($variance / $members->count());

        $riskScore = 100 - $avgTrustScore;

        // Facteur : Hétérogénéité (Si l'écart-type est fort, le risque augmente)
        if ($stdDev > 15) {
            $riskScore += 10;
            $factors[] = "Forte disparité des scores de confiance au sein du groupe.";
        }

        // Facteur : Ratio Payout/Cotisation
        $totalCotise = $group->contributions()->where('status', 'confirmed')->sum('amount_fcfa');
        $totalPayout = $group->payouts()->sum('total_amount_fcfa');
        
        if ($totalPayout > $totalCotise && $totalCotise > 0) {
            $riskScore += 15;
            $factors[] = "Le groupe a distribué plus qu'il n'a collecté (Risque de trésorerie).";
        }

        // Facteur : KYC non vérifié
        if ($unverifiedCount > 0) {
            $penalty = ($unverifiedCount / $members->count()) * 20;
            $riskScore += $penalty;
            $factors[] = "{$unverifiedCount} membre(s) sans NIP vérifié.";
        }

        // Facteur : Retards historiques
        $lateCount = $group->contributions()->where('is_late', true)->count();
        if ($lateCount > 0) {
            $riskScore += min($lateCount * 5, 30);
            $factors[] = "Historique de {$lateCount} retard(s) dans ce groupe.";
        }

        $riskScore = min(max($riskScore, 0), 100);

        $level = 'Faible';
        $color = 'green';
        if ($riskScore > 60) {
            $level = 'Critique';
            $color = 'red';
        } elseif ($riskScore > 40) {
            $level = 'Élevé';
            $color = 'orange';
        } elseif ($riskScore > 20) {
            $level = 'Modéré';
            $color = 'yellow';
        }

        return [
            'risk_score' => (int) $riskScore,
            'risk_level' => $level,
            'color_code' => $color,
            'factors' => $factors,
            'recommendation' => $this->getRecommendation($level)
        ];
    }

    private function getRecommendation(string $level): string
    {
        switch ($level) {
            case 'Critique': return "Risque de défaut très élevé. Activation du fonds de garantie impérative.";
            case 'Élevé': return "Surveillance accrue recommandée sur les prochaines échéances.";
            case 'Modéré': return "Groupe globalement stable, quelques points d'attention.";
            default: return "Groupe très sain. Confiance optimale.";
        }
    }
}
