<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PayoutController extends Controller
{
    #[OA\Get(
        path: "/api/v1/payouts",
        summary: "Lister tous mes ramassages reçus",
        description: "Affiche l'historique de tous les fonds que vous avez collectés dans vos différents groupes.",
        tags: ["Finances & Cotisations"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Historique des ramassages récupéré")
        ]
    )]
    public function index(Request $request)
    {
        $user = $request->user();
        $payouts = Payout::where('beneficiary_id', $user->id)->get();

        return response()->json($payouts);
    }

    #[OA\Get(
        path: "/api/v1/payouts/{payout}",
        summary: "Détails d'un ramassage spécifique (Preuve Blockchain)",
        description: "Fournit les détails d'un ramassage, incluant le montant net et le lien vers la transaction sur le réseau Polygon.",
        tags: ["Finances & Cotisations"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "payout", in: "path", required: true, description: "ID du ramassage", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Détails du ramassage")
        ]
    )]
    public function show(Request $request, Payout $payout)
    {
        if ($payout->beneficiary_id !== $request->user()->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        return response()->json($payout);
    }
}
