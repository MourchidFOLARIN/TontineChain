<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class KycService
{
    /**
     * Simule la vérification NPI auprès du service gouvernemental (ANIP)
     */
    public function verifyNpi($npi, User $user)
    {
        Log::info("Simulation KYC pour NPI: $npi");

        // Simulation d'un délai réseau
        usleep(500000); 

        // Dans une vraie app, on ferait un appel HTTP vers l'API de l'ANIP
        // Ici, on valide si le NPI a 10 chiffres (simulé)
        if (strlen($npi) >= 10) {
            $user->update([
                'kyc_status' => 'verified',
                'npi_hash' => hash('sha256', $npi),
            ]);
            return true;
        }

        $user->update(['kyc_status' => 'rejected']);
        return false;
    }
}
