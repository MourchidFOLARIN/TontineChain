<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Group;
use App\Services\PaymentService;
use Illuminate\Http\Request;
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
            ->where('status', 'pending')
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
    #[OA\Response(response: 200, description: "Lien de paiement généré")]
    public function initiate(Request $request, Contribution $contribution)
    {
        $user = $request->user();

        if ($contribution->user_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if ($contribution->status !== 'pending') {
            return response()->json(['error' => 'Cette cotisation est déjà en cours ou terminée.'], 400);
        }

        try {
            $paymentData = $this->payment->initiatePayment($contribution, $user);
            return response()->json($paymentData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
