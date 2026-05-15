<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Group;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class ContributionController extends Controller
{
    protected $payment;

    public function __construct(PaymentService $payment)
    {
        $this->payment = $payment;
    }

    #[OA\Get(
        path: "/api/v1/contributions/pending",
        summary: "Récupérer la liste des cotisations à payer",
        description: "Retourne toutes les cotisations dont le statut est 'pending', 'late' (en retard) ou 'processing' (en attente de validation).",
        tags: ["Finances & Cotisations"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Liste des cotisations récupérée",
                content: new OA\JsonContent(type: "array", items: new OA\Items(type: "object"))
            )
        ]
    )]
    public function pending(Request $request)
    {
        $user = $request->user();
        $contributions = Contribution::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'late', 'processing'])
            ->with('group')
            ->get();

        return response()->json($contributions);
    }

    #[OA\Post(
        path: "/api/v1/contributions/{contribution}/pay",
        summary: "Initier le paiement d'une cotisation via FedaPay",
        description: "Génère une session de paiement sécurisée. Le Front doit rediriger l'utilisateur vers l'URL fournie pour finaliser la transaction via Mobile Money (MTN/Moov).",
        tags: ["Finances & Cotisations"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "contribution", in: "path", required: true, description: "ID de la cotisation", schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Session de paiement créée",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 45),
                        new OA\Property(property: "reference", type: "string", example: "TONT-123456"),
                        new OA\Property(property: "url", type: "string", example: "https://checkout.fedapay.com/..."),
                        new OA\Property(property: "demo_notice", type: "object")
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Action non autorisée sur cette cotisation")
        ]
    )]
    public function initiate(Request $request, Contribution $contribution)
    {
        $user = $request->user();

        if ($contribution->user_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if (! in_array($contribution->status, ['pending', 'late', 'processing'], true)) {
            return response()->json(['error' => 'Cette cotisation est déjà terminée.'], 400);
        }

        try {
            $paymentData = $this->payment->initiatePayment($contribution, $user);
            $paymentData['demo_notice'] = [
                'is_simulation' => true,
                'message' => "MODÈLE DE SIMULATION : Le lien de paiement FedaPay a été généré (Réf: " . ($paymentData['reference'] ?? 'REF_DEMO') . "). En production, l'utilisateur est redirigé vers MTN/Moov Money pour valider le débit de " . number_format($contribution->amount_fcfa, 0, ',', ' ') . " FCFA."
            ];
            return response()->json($paymentData);
        } catch (\Exception $e) {
            Log::error('Contribution initiate payment failed', ['exception' => $e->getMessage()]);

            $message = config('app.debug') ? $e->getMessage() : 'Erreur lors de l\'initiation du paiement.';

            return response()->json(['error' => $message], 500);
        }
    }
}
