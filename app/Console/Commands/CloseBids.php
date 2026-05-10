<?php

namespace App\Console\Commands;

use App\Models\Bid;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CloseBids extends Command
{
    protected $signature = 'tontine:close-bids';
    protected $description = 'Clôture les enchères pour le cycle actuel et désigne le gagnant';

    public function handle()
    {
        $this->info('Vérification des enchères en cours...');

        $groups = Group::where('status', 'active')
            ->where('payout_method', 'bidding')
            ->get();

        foreach ($groups as $group) {
            $this->info("Traitement du groupe : {$group->name}");

            // Vérifier si un gagnant a déjà été désigné pour ce cycle
            $hasWinner = GroupMember::where('group_id', $group->id)
                ->where('has_received', true)
                ->where('cycle_received', $group->current_cycle)
                ->exists();

            if ($hasWinner) {
                $this->line("Un gagnant existe déjà pour le cycle {$group->current_cycle}.");
                continue;
            }

            // Trouver la meilleure offre pour ce cycle
            $bestBid = Bid::where('group_id', $group->id)
                ->where('cycle_number', $group->current_cycle)
                ->orderByDesc('discount_amount')
                ->first();

            if (!$bestBid) {
                $this->warn("Aucune offre trouvée pour le cycle {$group->current_cycle}.");
                // Optionnel : Sélectionner un membre aléatoire ou reporter ?
                // Ici on attend des offres.
                continue;
            }

            DB::transaction(function () use ($group, $bestBid) {
                // Désigner le gagnant
                GroupMember::where('group_id', $group->id)
                    ->where('user_id', $bestBid->user_id)
                    ->update([
                        'has_received' => true,
                        'cycle_received' => $group->current_cycle
                    ]);

                // Enregistrer l'enchère comme gagnante (si champ existant, sinon log)
                Log::info("Gagnant Enchère : Utilisateur {$bestBid->user_id} a gagné le cycle {$group->current_cycle} du groupe {$group->id} avec une remise de {$bestBid->discount_amount} FCFA.");
                
                $this->info("Gagnant désigné : Utilisateur {$bestBid->user_id} avec {$bestBid->discount_amount} FCFA.");
            });
        }

        $this->info('Clôture des enchères terminée.');
    }
}
