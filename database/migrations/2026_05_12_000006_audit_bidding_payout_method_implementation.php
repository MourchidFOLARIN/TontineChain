<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * IMPORTANT : Cette migration est un audit de réconciliation.
     * 
     * Le mode 'bidding' est défini en base mais la logique n'est pas complètement implémentée
     * dans WebhookController::checkAndReleasePayout().
     * 
     * À implémenter :
     * 1. Quand payout_method = 'bidding', chercher le bid gagnant du cycle
     * 2. Appliquer la réduction du discount_amount au bénéficiaire
     * 3. Ajouter le discount_amount au fonds d'assurance du groupe
     */
    public function up(): void
    {
        // Pas de modifications schéma, c'est une note d'audit
    }

    public function down(): void
    {
        // Rien à défaire
    }
};
