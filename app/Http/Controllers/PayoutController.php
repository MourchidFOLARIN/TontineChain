<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PayoutController extends Controller
{
    #[OA\Get(
        path: "/api/v1/payouts",
        summary: "Lister les paiements reçus (ramassages)",
        security: [["sanctum" => []]],
        tags: ["Payouts"],
        responses: [
            new OA\Response(response: 200, description: "Liste des paiements du membre")
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
        summary: "Détails d'un paiement spécifique",
        security: [["sanctum" => []]],
        tags: ["Payouts"],
        parameters: [
            new OA\PathParameter(name: "payout", required: true, description: "ID du paiement", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Détails du paiement"),
            new OA\Response(response: 403, description: "Non autorisé")
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
