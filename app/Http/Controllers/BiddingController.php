<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Group;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BiddingController extends Controller
{
    #[OA\Post(
        path: "/api/v1/groups/{group}/bid",
        summary: "Soumettre une offre d'enchère pour le tour actuel",
        tags: ["Enchères"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "group", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "discount_amount", type: "number", example: 5000, description: "Montant que vous êtes prêt à laisser au groupe")
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: "Offre enregistrée")]
    public function submitBid(Request $request, Group $group)
    {
        $user = $request->user();

        if ($group->payout_method !== 'bidding') {
            return response()->json(['error' => "Ce groupe n'utilise pas le mode enchères"], 400);
        }

        if ($group->status !== 'active') {
            return response()->json(['error' => "Le groupe n'est pas actif"], 400);
        }

        $request->validate([
            'discount_amount' => 'required|numeric|min:0|max:' . ($group->contribution_amount * $group->max_members / 2),
        ]);

        // Vérifier si le membre a déjà reçu un payout
        $membership = $group->members()->where('user_id', $user->id)->first();
        if ($membership->has_received) {
            return response()->json(['error' => "Vous avez déjà reçu votre ramassage"], 400);
        }

        $bid = Bid::updateOrCreate(
            [
                'group_id' => $group->id,
                'user_id' => $user->id,
                'cycle_number' => $group->current_cycle,
            ],
            ['discount_amount' => $request->discount_amount]
        );

        return response()->json([
            'message' => 'Offre enregistrée avec succès',
            'bid' => $bid
        ], 201);
    }

    #[OA\Get(
        path: "/api/v1/groups/{group}/bids",
        summary: "Voir les offres du cycle actuel",
        tags: ["Enchères"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(response: 200, description: "Liste des offres")]
    public function index(Group $group)
    {
        $bids = $group->bids()->where('cycle_number', $group->current_cycle)->orderByDesc('discount_amount')->get();
        return response()->json($bids);
    }
}
