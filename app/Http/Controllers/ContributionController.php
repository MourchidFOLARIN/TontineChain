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
        summary: "Mes cotisations en attente",
        tags: ["Cotisations"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(response: 200, description: "Liste des cotisations")]
    public function pending(Request $request)
    {
        $user = $request->user();
        $contributions = Contribution::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'late'])
            ->with('group')
            ->get();

        return response()->json($contributions);
    }

    #[OA\Post(
        path: "/api/v1/contributions/{contribution}/pay",
        summary: "Initier le paiement d'une cotisation",
        tags: ["Cotisations"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(name: "contribution", in: "path", required: true, schema: new OA\Schema(type: "string"))]
    #[OA\Response(
        response: 200, 
        description: "Lien de paiement généré",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "id", type: "integer"),
                new OA\Property(property: "reference", type: "string"),
                new OA\Property(property: "url", type: "string"),
                new OA\Property(
                    property: "demo_notice", 
                    type: "object",
                    properties: [
                        new OA\Property(property: "is_simulation", type: "boolean"),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            ]
        )
    )]
    public function initiate(Request $request, Contribution $contribution)
    {
        $user = $request->user();

        if ($contribution->user_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if (! in_array($contribution->status, ['pending', 'late'], true)) {
            return response()->json(['error' => 'Cette cotisation est déjà en cours ou terminée.'], 400);
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
