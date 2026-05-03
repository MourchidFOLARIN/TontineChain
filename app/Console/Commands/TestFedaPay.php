<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use FedaPay\FedaPay;
use FedaPay\Transaction;

class TestFedaPay extends Command
{
    protected $signature = 'fedapay:test';
    protected $description = 'Test FedaPay connection with provided keys';

    public function handle()
    {
        $this->info('Connexion à FedaPay en cours...');

        FedaPay::setApiKey(env('FEDAPAY_API_KEY'));
        FedaPay::setEnvironment(env('FEDAPAY_ENV', 'sandbox'));

        try {
            // Utilisation de la recherche (méthode recommandée)
            $transactions = Transaction::search(['per_page' => 1]);
            
            $this->success('✅ Connexion réussie !');
            $this->line('Clé valide. Environnement : ' . env('FEDAPAY_ENV'));
            $this->line('Nombre de transactions trouvées : ' . count($transactions));

        } catch (\Exception $e) {
            $this->error('❌ Échec de la connexion.');
            $this->error($e->getMessage());
        }
    }

    protected function success($message)
    {
        $this->line("<info>$message</info>");
    }
}
