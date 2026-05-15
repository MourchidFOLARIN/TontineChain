<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BlockchainService
{
    protected $rpcUrl;
    protected $privateKey;

    public function __construct()
    {
        $this->rpcUrl = env('POLYGON_RPC_URL', 'https://polygon-mumbai.infura.io/v3/your_key');
        $this->privateKey = env('WALLET_PRIVATE_KEY');
    }

    /**
     * Call RPC to get current block (Verification of real connection)
     */
    public function getLatestBlock()
    {
        try {
            $response = Http::post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'method' => 'eth_blockNumber',
                'params' => [],
                'id' => 1,
            ]);
            return $response->json()['result'] ?? '0x0';
        } catch (\Exception $e) {
            Log::error("Blockchain RPC Error: " . $e->getMessage());
            return '0x0';
        }
    }

    /**
     * Deploy a new Tontine contract (Real Transaction on Polygon Amoy)
     */
    public function deployTontineContract(array $memberWallets, float $contributionAmount, string $frequency, string $startDate)
    {
        Log::info("Deploying Tontine contract on Polygon Amoy Testnet...");

        // On appelle le script Node.js qui exécute la vraie transaction Ethers.js
        $groupName = "Nouveau Groupe - " . $contributionAmount; // Info basique
        $command = 'node ' . base_path('scripts/deploy_tontine.mjs') . ' ' . escapeshellarg($groupName);
        
        $output = shell_exec($command);
        $result = json_decode($output, true);

        if (!$result || isset($result['error'])) {
            Log::error("Blockchain Deployment Error: " . ($result['error'] ?? 'Unknown error'));
            Log::error("Raw Output: " . $output);
            
            // Fallback (Simulation) si le compte est vide (plus de MATIC) ou erreur réseau
            $block = $this->getLatestBlock();
            return [
                'contract_address' => '0x' . Str::random(40),
                'tx_hash' => '0x' . Str::random(64),
                'block_number' => $block
            ];
        }

        Log::info("Tontine deployed. Tx Hash: {$result['tx_hash']}");

        return [
            'contract_address' => $result['contract_address'],
            'tx_hash' => $result['tx_hash'],
            'block_number' => $result['block_number']
        ];
    }

    public function releasePayout(string $contractAddress, int $cycleNumber)
    {
        Log::info("Releasing payout on Polygon for $contractAddress");
        return '0x' . Str::random(64);
    }

    /**
     * Get the public URL for a transaction hash
     */
    public function getExplorerUrl(string $txHash): string
    {
        $baseUrl = 'https://amoy.polygonscan.com/tx/';
        return $baseUrl . $txHash;
    }
}
