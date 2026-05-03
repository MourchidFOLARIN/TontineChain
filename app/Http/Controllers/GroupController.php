<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\BlockchainService;
use App\Services\RiskAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class GroupController extends Controller
{
    protected $blockchain;
    protected $riskService;

    public function __construct(BlockchainService $blockchain, RiskAnalysisService $riskService)
    {
        $this->blockchain = $blockchain;
        $this->riskService = $riskService;
    }

    #[OA\Get(
        path: "/api/v1/groups",
        summary: "Lister mes groupes",
        tags: ["Groupes"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(response: 200, description: "Liste des groupes")]
    public function index(Request $request)
    {
        $user = $request->user();
        $groups = Group::where('creator_id', $user->id)
            ->orWhereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();

        return response()->json($groups);
    }

    #[OA\Post(
        path: "/api/v1/groups",
        summary: "Créer une nouvelle tontine",
        tags: ["Groupes"],
        security: [["sanctum" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "Tontine Famille"),
                new OA\Property(property: "contribution_amount", type: "number", example: 10000),
                new OA\Property(property: "max_members", type: "integer", example: 5),
                new OA\Property(property: "frequency", type: "string", enum: ["weekly", "biweekly", "monthly"]),
                new OA\Property(property: "payout_method", type: "string", enum: ["sequential", "random", "bidding"]),
                new OA\Property(property: "insurance_opt_in", type: "boolean", example: true),
                new OA\Property(property: "start_date", type: "string", format: "date-time")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Groupe créé")]
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'contribution_amount' => 'required|numeric|min:1000',
            'max_members' => 'required|integer|min:2|max:50',
            'frequency' => 'required|in:weekly,biweekly,monthly',
            'payout_method' => 'required|in:sequential,random,bidding',
            'insurance_opt_in' => 'sometimes|boolean',
            'start_date' => 'required|date|after:now',
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($request, $user) {
            $group = Group::create([
                'name' => $request->name,
                'creator_id' => $user->id,
                'contribution_amount' => $request->contribution_amount,
                'max_members' => $request->max_members,
                'frequency' => $request->frequency,
                'payout_method' => $request->payout_method,
                'insurance_percent' => $request->input('insurance_opt_in', true) ? 1.0 : 0.0,
                'total_cycles' => $request->max_members,
                'start_date' => $request->start_date,
                'status' => 'pending',
            ]);

            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $user->id,
                'position' => 1,
                'status' => 'active',
            ]);

            $group->increment('current_members');

            return response()->json($group, 201);
        });
    }

    #[OA\Get(
        path: "/api/v1/groups/{group}",
        summary: "Détails d'un groupe",
        tags: ["Groupes"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(name: "group", in: "path", required: true, schema: new OA\Schema(type: "string"))]
    #[OA\Response(response: 200, description: "Détails du groupe")]
    public function show(Group $group)
    {
        $group->load(['creator', 'members.user']);
        return response()->json($group);
    }

    #[OA\Post(
        path: "/api/v1/groups/{group}/start",
        summary: "Démarrer la tontine",
        tags: ["Groupes"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(name: "group", in: "path", required: true, schema: new OA\Schema(type: "string"))]
    #[OA\Response(response: 200, description: "Tontine démarrée")]
    public function start(Request $request, Group $group)
    {
        $user = $request->user();

        if ($group->creator_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if ($group->status !== 'pending') {
            return response()->json(['error' => 'Groupe déjà démarré'], 400);
        }

        if ($group->current_members < $group->max_members) {
            return response()->json(['error' => "Groupe incomplet"], 400);
        }

        return DB::transaction(function () use ($group) {
            $members = $group->members()->where('status', 'active')->get();

            if ($group->payout_method === 'random') {
                $shuffledMembers = $members->shuffle();
                foreach ($shuffledMembers as $index => $member) {
                    $member->update(['position' => $index + 1]);
                }
            } 

            $memberWallets = $group->members()->with('user')->get()->pluck('user.wallet_address')->toArray();

            $deployment = $this->blockchain->deployTontineContract(
                $memberWallets,
                $group->contribution_amount,
                $group->frequency,
                $group->start_date
            );

            $group->update([
                'contract_address' => $deployment['contract_address'],
                'contract_tx_hash' => $deployment['tx_hash'],
                'status' => 'active',
                'current_cycle' => 1,
                'next_due_date' => Carbon::parse($group->start_date),
            ]);

            return response()->json([
                'message' => "Tontine démarrée",
                'contract_address' => $group->contract_address
            ]);
        });
    }

    #[OA\Post(
        path: "/api/v1/groups/{group}/invite",
        summary: "Inviter un membre",
        tags: ["Groupes"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(response: 200, description: "Invitation envoyée")]
    public function invite(Request $request, Group $group)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^\+?[0-9]{8,15}$/',
        ]);

        if ($group->creator_id !== $request->user()->id) {
            return response()->json(['error' => 'Seul le créateur peut inviter des membres'], 403);
        }

        if ($group->current_members >= $group->max_members) {
            return response()->json(['error' => 'Groupe déjà complet'], 400);
        }
        
        $user = User::where('phone', $request->phone)->first();
        
        if ($user && GroupMember::where('group_id', $group->id)->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Déjà membre ou invité'], 400);
        }

        if (!$user) {
            $user = User::create([
                'phone' => $request->phone,
                'full_name' => 'Invité',
                'kyc_status' => 'none',
                'score_confiance' => 100,
            ]);
        }

        $trustLevel = $this->getTrustLevel($user);

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'position' => $group->current_members + 1,
            'status' => 'invited',
        ]);

        $group->increment('current_members');

        return response()->json([
            'message' => 'Invitation envoyée',
            'member_analysis' => [
                'name' => $user->full_name,
                'score' => $user->score_confiance,
                'trust_level' => $trustLevel['label'],
                'trust_message' => $trustLevel['message']
            ]
        ]);
    }

    private function getTrustLevel($user)
    {
        $score = $user->score_confiance;
        if ($score >= 90) return ['label' => 'Elite', 'message' => 'Membre Or 🏆'];
        if ($score >= 75) return ['label' => 'Fiable', 'message' => 'Membre Argent 🥈'];
        if ($score >= 50) return ['label' => 'Moyen', 'message' => 'Membre Standard 🥉'];
        return ['label' => 'À risque', 'message' => 'Avertissement ⚠️'];
    }

    #[OA\Post(
        path: "/api/v1/groups/{group}/join",
        summary: "Rejoindre un groupe",
        tags: ["Groupes"]
    )]
    #[OA\Response(response: 200, description: "Rejoint avec succès")]
    public function join(Request $request, Group $group)
    {
        $user = $request->user();
        $membership = GroupMember::where('group_id', $group->id)->where('user_id', $user->id)->where('status', 'invited')->first();
        if (!$membership) return response()->json(['error' => "Invitation non trouvée"], 404);
        $membership->update(['status' => 'active']);
        return response()->json(['message' => 'Bienvenue dans le groupe !']);
    }

    #[OA\Get(
        path: "/api/v1/groups/{group}/stats",
        summary: "Statistiques & Analyse de Risque IA",
        tags: ["Groupes"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "group", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Stats et prédiction de risque")
        ]
    )]
    public function stats(Group $group)
    {
        $totalCollected = $group->contributions()->where('status', 'confirmed')->sum('amount_fcfa');
        $totalPayouts = $group->payouts()->where('status', 'completed')->sum('total_amount_fcfa');
        $activeMembersCount = $group->members()->where('status', 'active')->count();
        
        // Analyse de risque IA
        $riskAnalysis = $this->riskService->analyzeGroupRisk($group);

        return response()->json([
            'group_id' => $group->id,
            'name' => $group->name,
            'payout_method' => $group->payout_method,
            'insurance' => [
                'fund_fcfa' => (int) $group->insurance_fund,
                'status' => $group->insurance_percent > 0 ? 'Actif' : 'Désactivé',
            ],
            'stats' => [
                'total_collected_fcfa' => (int) $totalCollected,
                'total_payouts_fcfa' => (int) $totalPayouts,
                'active_members' => $activeMembersCount,
                'current_cycle' => $group->current_cycle,
            ],
            'ai_risk_prediction' => $riskAnalysis
        ]);
    }
}
