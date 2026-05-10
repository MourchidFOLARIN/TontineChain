<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Vote;
use App\Models\VoteRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class VoteController extends Controller
{
    #[OA\Post(
        path: "/api/v1/groups/{group}/propose-swap",
        summary: "Proposer un échange de position de ramassage",
        tags: ["Gouvernance"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "group", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "target_user_id", type: "string", description: "ID de l'utilisateur avec qui échanger")
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201, 
        description: "Proposition créée",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "id", type: "string"),
                new OA\Property(property: "status", type: "string"),
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
    public function proposeSwap(Request $request, Group $group)
    {
        $user = $request->user();

        // Vérifier que le groupe est actif
        if ($group->status !== 'active') {
            return response()->json(['error' => 'Le groupe doit être actif pour voter'], 400);
        }

        $request->validate([
            'target_user_id' => 'required|exists:users,id',
        ]);

        $targetUserId = $request->target_user_id;

        // Créer la proposition
        $vote = Vote::create([
            'group_id' => $group->id,
            'creator_id' => $user->id,
            'type' => 'swap_positions',
            'proposal_data' => [
                'user_a' => $user->id,
                'user_b' => $targetUserId
            ],
            'required_votes' => ceil($group->max_members / 2) + 1,
            'status' => 'pending',
            'expires_at' => now()->addHours(24),
        ]);

        $vote->demo_notice = [
            'is_simulation' => true,
            'message' => "MODÈLE DE SIMULATION : Une notification de vote a été envoyée à tous les membres du groupe par SMS et WhatsApp. Ils peuvent maintenant voter pour approuver ou rejeter votre demande d'échange."
        ];

        return response()->json($vote, 201);
    }

    #[OA\Post(
        path: "/api/v1/votes/{vote}/cast",
        summary: "Voter pour une proposition",
        tags: ["Gouvernance"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "vote", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "choice", type: "boolean", description: "true = Pour, false = Contre")
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200, 
        description: "Vote enregistré",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string"),
                new OA\Property(property: "current_status", type: "string"),
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
    public function castVote(Request $request, Vote $vote)
    {
        $user = $request->user();

        if ($vote->status !== 'pending' || $vote->expires_at->isPast()) {
            return response()->json(['error' => 'Ce vote est clos ou expiré'], 400);
        }

        if (VoteRecord::where('vote_id', $vote->id)->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Vous avez déjà voté'], 400);
        }

        $request->validate(['choice' => 'required|boolean']);

        return DB::transaction(function () use ($request, $vote, $user) {
            VoteRecord::create([
                'vote_id' => $vote->id,
                'user_id' => $user->id,
                'choice' => $request->choice
            ]);

            if ($request->choice) {
                $vote->increment('yes_votes');
            } else {
                $vote->increment('no_votes');
            }

            if ($vote->yes_votes >= $vote->required_votes) {
                $vote->update(['status' => 'approved']);
                $this->applySwap($vote);
            } elseif ($vote->no_votes > ($vote->group->max_members - $vote->required_votes)) {
                $vote->update(['status' => 'rejected']);
            }

            return response()->json([
                'message' => 'Vote enregistré', 
                'current_status' => $vote->status,
                'demo_notice' => [
                    'is_simulation' => true,
                    'message' => "MODÈLE DE SIMULATION : Votre vote a été enregistré. Si le quorum est atteint, la nouvelle position de ramassage sera ancrée sur la blockchain."
                ]
            ]);
        });
    }

    #[OA\Get(
        path: "/api/v1/votes",
        summary: "Lister les votes de l'utilisateur",
        tags: ["Gouvernance"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Liste des votes (créés ou reçus)")
        ]
    )]
    public function index(Request $request)
    {
        $user = $request->user();
        $votes = Vote::where('creator_id', $user->id)
            ->orWhereHas('group.members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('group')
            ->get();

        return response()->json($votes);
    }

    #[OA\Get(
        path: "/api/v1/votes/{vote}",
        summary: "Détails d'un vote",
        tags: ["Gouvernance"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "vote", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Détails du vote et du groupe associé"),
            new OA\Response(response: 403, description: "Non autorisé")
        ]
    )]
    public function show(Request $request, Vote $vote)
    {
        $user = $request->user();

        if (! $vote->group->members()->where('user_id', $user->id)->exists() && $vote->creator_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        return response()->json($vote->load('group'));
    }

    protected function applySwap(Vote $vote)
    {
        $data = $vote->proposal_data;
        $memberA = GroupMember::where('group_id', $vote->group_id)->where('user_id', $data['user_a'])->first();
        $memberB = GroupMember::where('group_id', $vote->group_id)->where('user_id', $data['user_b'])->first();

        if ($memberA && $memberB) {
            $posA = $memberA->position;
            $posB = $memberB->position;

            $memberA->update(['position' => $posB]);
            $memberB->update(['position' => $posA]);
            
            // Note: En production, on enverrait aussi une transaction Blockchain pour mettre à jour l'ordre
        }
    }
}
