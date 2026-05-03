<?php

namespace App\Http\Controllers;

use FedaPay\FedaPay;
use FedaPay\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    /**
     * Test la connexion à FedaPay
     */
    public function testFedapay()
    {
        try {
            FedaPay::setApiKey(env('FEDAPAY_API_KEY'));
            FedaPay::setEnvironment(env('FEDAPAY_ENV', 'sandbox'));

            // On essaie de lister les dernières transactions pour voir si la clé est valide
            $transactions = Transaction::all(['limit' => 1]);

            return response()->json([
                'status' => 'success',
                'message' => 'Connexion à FedaPay établie avec succès !',
                'environment' => env('FEDAPAY_ENV'),
                'api_key_last_chars' => '***' . substr(env('FEDAPAY_API_KEY'), -4)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la connexion à FedaPay : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test l'envoi de SMS via Infobip
     */
    public function testInfobip(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        try {
            $sms = new \App\Services\SmsService();
            $phone = $sms->normalizePhone($request->phone);
            $success = $sms->sendSms($phone, "TontineChain : Test de connexion réussi ! 🚀");

            if ($success) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Le SMS de test a été envoyé à ' . $phone,
                    'note' => 'Vérifiez vos logs si vous ne recevez rien (si la clé est encore en mode mock).'
                ]);
            } else {
                return response()->json(['status' => 'error', 'message' => "L'envoi a échoué. Vérifiez votre clé API Infobip."], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Test la connexion à la Blockchain Polygon
     */
    public function testBlockchain()
    {
        try {
            $blockchain = new \App\Services\BlockchainService();
            $blockHex = $blockchain->getLatestBlock();
            $blockNumber = hexdec($blockHex);

            if ($blockNumber > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Connexion au réseau Polygon établie !',
                    'latest_block_hex' => $blockHex,
                    'latest_block_decimal' => $blockNumber,
                    'rpc_url' => env('POLYGON_RPC_URL') ?: 'URL par défaut (Mumbai)'
                ]);
            } else {
                return response()->json(['status' => 'error', 'message' => "Impossible de lire le dernier bloc. Vérifiez votre URL RPC."], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Test les notifications Telegram
     */
    public function testTelegram()
    {
        try {
            $telegram = new \App\Services\TelegramService();
            $success = $telegram->sendMessage("🚀 *TontineChain Debug* : Connexion au Bot réussie ! Le flux d'activité est prêt.");

            if ($success) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Message de test envoyé sur Telegram avec succès !'
                ]);
            } else {
                return response()->json(['status' => 'error', 'message' => "L'envoi Telegram a échoué. Vérifiez votre TOKEN et CHAT_ID."], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Test l'envoi d'Email via Infobip
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $sms = new \App\Services\SmsService();
            $success = $sms->sendEmail($request->email, "TontineChain : Test d'envoi d'email réussi ! 📧");

            if ($success) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'L\'email de test a été envoyé à ' . $request->email,
                    'note' => 'Si vous ne recevez rien, vérifiez que MAIL_MAILER=smtp et non log.'
                ]);
            } else {
                return response()->json([
                    'status' => 'error', 
                    'message' => "L'envoi d'email a échoué. Vérifiez vos réglages SMTP sur Render."
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Erreur technique : ' . $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 500) // Pour plus de détails
            ], 500);
        }
    }
}
