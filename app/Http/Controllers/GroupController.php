<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\TontineNotification;
use App\Mail\TontineNotificationMail;
use App\Services\BlockchainService;
use App\Services\RiskAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\TontineContractMail;
use Barryvdh\DomPDF\Facade\Pdf;
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
    #[OA\Response(
        response: 200, 
        description: "Tontine démarrée",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string"),
                new OA\Property(property: "contract_address", type: "string"),
                new OA\Property(
                    property: "demo_notice", 
                    type: "object",
                    properties: [
                        new OA\Property(property: "is_simulation", type: "boolean"),
                        new OA\Property(property: "blockchain_tx", type: "string"),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            ]
        )
    )]
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

        return DB::transaction(function () use ($group, $user) {
            // Appliquer la langue préférée de l'utilisateur pour l'email
            app()->setLocale($user->preferred_language ?? 'fr');

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

            try {
                $group->load(['creator', 'members.user']);
                $pdf = Pdf::loadView('pdf.tontine_contract', ['group' => $group]);
                $pdfContent = $pdf->output();

                $recipientEmail = $group->creator->email ?? 'mourchidolawale@gmail.com';
                Mail::to($recipientEmail)->send(new TontineContractMail($group, $pdfContent));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Erreur génération/envoi PDF: " . $e->getMessage());
            }

            // Notifier tous les membres par Email (Réel)
            foreach ($group->members as $member) {
                if ($member->user && $member->user->email) {
                    $userLocale = $member->user->preferred_language ?? 'fr';
                    
                    Mail::to($member->user->email)
                        ->locale($userLocale)
                        ->queue(new TontineNotificationMail(
                            __('messages.contract_subject'),
                            __('messages.contract_body') . " (Tontine: " . $group->name . ")"
                        ));
                }
            }

            return response()->json([
                'message' => "Tontine démarrée",
                'contract_address' => $group->contract_address,
                'demo_notice' => [
                    'is_simulation' => true,
                    'blockchain_tx' => $group->contract_tx_hash,
                    'message' => "MODÈLE DE SIMULATION : Le contrat intelligent a été déployé sur Polygon (Tx: " . substr($group->contract_tx_hash, 0, 15) . "...). Le contrat PDF a été généré et envoyé par Email au créateur et aux membres. En production, chaque membre reçoit une copie certifiée."
                ]
            ]);
        });
    }

    #[OA\Post(
        path: "/api/v1/groups/{group}/invite",
        summary: "Inviter un membre",
        tags: ["Groupes"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(
        response: 200, 
        description: "Invitation envoyée",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string"),
                new OA\Property(property: "member_analysis", type: "object"),
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
    public function invite(Request $request, Group $group)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^\+?[0-9]{8,15}$/',
            'email' => 'sometimes|email',
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
                'email' => $request->email ?? null,
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

        // --- VÉRIFICATION SI LE GROUPE EST AU COMPLET ---
        if ($group->current_members >= $group->max_members) {
            $creator = $group->creator;
            if ($creator) {
                // 1. Notification In-App
                TontineNotification::create([
                    'user_id' => $creator->id,
                    'type' => 'cycle_started', // On réutilise ce type pour dire que c'est prêt
                    'message' => "Votre groupe '" . $group->name . "' est maintenant au complet ! Vous pouvez officiellement démarrer la tontine.",
                    'channel' => 'in_app',
                ]);

                // 2. Notification Email
                if ($creator->email) {
                    $content = "Félicitations ! Votre groupe de tontine '" . $group->name . "' a atteint sa capacité maximale (" . $group->max_members . " membres).\n\n" .
                               "Tous les membres sont prêts. Vous pouvez maintenant vous connecter pour activer le Smart Contract et démarrer les cotisations.";
                    
                    Mail::to($creator->email)->queue(new TontineNotificationMail(
                        "Votre groupe est au complet : " . $group->name,
                        $content,
                        env('APP_URL') . "/groups/" . $group->id
                    ));
                }
            }
        }

        // --- ENVOI EMAIL RÉEL À L'INVITÉ ---
        $recipientEmail = $request->email ?? $user->email;
        if ($recipientEmail) {
            $invitationLink = env('APP_URL', 'https://tontinechain.bj') . "/join/" . $group->code;
            
            Mail::to($recipientEmail)
                ->locale($user->preferred_language ?? 'fr')
                ->send(new TontineNotificationMail(
                    __('messages.welcome'),
                    __('messages.payment_reminder') . " (Tontine: " . $group->name . ", Code: " . $group->code . ")",
                    $invitationLink
                ));
        }

        return response()->json([
            'message' => 'Invitation envoyée',
            'member_analysis' => [
                'name' => $user->full_name,
                'score' => $user->score_confiance,
                'trust_level' => $trustLevel['label'],
                'trust_message' => $trustLevel['message']
            ],
            'demo_notice' => [
                'is_simulation' => true,
                'message' => "MODÈLE DE SIMULATION : L'invitation a été envoyée par Email à $recipientEmail. En production, un SMS/WhatsApp est aussi envoyé avec un guide vocal."
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

        // Message système dans le chat
        \App\Http\Controllers\MessageController::sendSystemMessage(
            $group->id, 
            "✨ " . $user->full_name . " a officiellement rejoint le cercle. Bienvenue !"
        );

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

    #[OA\Get(
        path: "/api/v1/groups/{group}/contract",
        summary: "Télécharger le contrat PDF de la tontine",
        tags: ["Groupes"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "group", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Fichier PDF du contrat"),
            new OA\Response(response: 404, description: "Contrat non disponible (tontine non démarrée)")
        ]
    )]
    public function downloadContract(Group $group)
    {
        if ($group->status === 'pending') {
            return response()->json(['error' => 'La tontine n\'a pas encore démarré.'], 404);
        }

        $group->load(['creator', 'members.user']);
        $pdf = Pdf::loadView('pdf.tontine_contract', ['group' => $group]);
        
        return $pdf->download('Contrat_Tontine_'.$group->name.'.pdf');
    }
}
