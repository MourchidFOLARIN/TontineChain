<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: "/api/v1/users/me",
        summary: "Mon profil",
        tags: ["Utilisateurs"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(response: 200, description: "Détails du profil")]
    public function me(Request $request)
    {
        $user = $request->user();
        $user->loadCount(['memberships', 'payouts']);

        // Set locale for translations
        app()->setLocale($user->preferred_language ?? 'fr');

        $response = $user->toArray();
        $response['greetings'] = [
            'text' => __('messages.welcome'),
            'audio_url' => __('messages.audio_guide_url'),
        ];

        return response()->json($response);
    }

    #[OA\Patch(
        path: "/api/v1/users/me",
        summary: "Mettre à jour mon profil (Nom, Prénom, Profession, NIP)",
        tags: ["Utilisateurs"],
        security: [["sanctum" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "first_name", type: "string", example: "Jean"),
                new OA\Property(property: "last_name", type: "string", example: "Houenou"),
                new OA\Property(property: "email", type: "string", example: "jean@example.com"),
                new OA\Property(property: "profession", type: "string", example: "Commerçant"),
                new OA\Property(property: "npi", type: "string", example: "1234567890123"),
                new OA\Property(property: "preferred_language", type: "string", example: "fon")
            ]
        )
    )]
    #[OA\Response(
        response: 200, 
        description: "Profil mis à jour",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string"),
                new OA\Property(property: "user", type: "object"),
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
    public function update(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
            'profession' => 'sometimes|string|max:150',
            'npi' => 'sometimes|string|min:5|max:20',
            'preferred_language' => 'sometimes|string|in:fr,fon,yor',
            'full_name' => 'sometimes|string|max:200',
        ]);

        if (isset($validated['npi'])) {
            $npiHash = hash('sha256', $validated['npi']);
            
            // Vérifier si le NIP est déjà utilisé par un AUTRE utilisateur
            $exists = \App\Models\User::where('npi_hash', $npiHash)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($exists) {
                return response()->json(['error' => 'Ce NIP est déjà enregistré par un autre utilisateur.'], 422);
            }

            $validated['npi_hash'] = $npiHash;
            unset($validated['npi']);
        }

        if (isset($validated['first_name']) && isset($validated['last_name'])) {
            $validated['full_name'] = $validated['first_name'] . ' ' . $validated['last_name'];
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => $user,
            'demo_notice' => [
                'is_simulation' => true,
                'message' => "MODÈLE DE SIMULATION : Le NIP a été vérifié auprès des services de l'ANIP. Votre identité est maintenant certifiée sur la blockchain."
            ]
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|unique:users,phone,' . $user->id,
        ]);

        $validated['full_name'] = $validated['first_name'] . ' ' . $validated['last_name'];
        $user->update($validated);

        return response()->json([
            'message' => 'Profil complété avec succès',
            'user' => $user,
            'demo_notice' => [
                'is_simulation' => true,
                'message' => "INSCRIPTION TERMINÉE : Votre compte est maintenant actif. Votre numéro " . $validated['phone'] . " sera utilisé pour vos futurs retraits via FedaPay."
            ]
        ]);
    }

    #[OA\Get(
        path: "/api/v1/users/me/score",
        summary: "Mon score de confiance et incidents",
        tags: ["Utilisateurs"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(response: 200, description: "Score et historique")]
    public function score(Request $request)
    {
        $user = $request->user();
        $incidents = Incident::where('user_id', $user->id)->orderBy('occurred_at', 'desc')->get();

        return response()->json([
            'score_confiance' => $user->score_confiance,
            'incidents' => $incidents
        ]);
    }

    #[OA\Get(
        path: "/api/v1/users/me/payouts",
        summary: "Mon historique de gains (Payouts)",
        tags: ["Utilisateurs"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Liste des gains")
        ]
    )]
    public function payouts(Request $request)
    {
        $user = $request->user();
        $payouts = $user->payouts()->with('group')->orderBy('completed_at', 'desc')->get();

        return response()->json($payouts);
    }

    #[OA\Get(
        path: "/api/v1/users/leaderboard",
        summary: "Top 10 des membres les plus fiables",
        tags: ["Utilisateurs"],
        responses: [
            new OA\Response(response: 200, description: "Classement")
        ]
    )]
    public function leaderboard()
    {
        $topUsers = \App\Models\User::where('is_active', true)
            ->orderBy('score_confiance', 'desc')
            ->limit(10)
            ->get(['id', 'full_name', 'score_confiance']);

        return response()->json($topUsers);
    }

    #[OA\Get(
        path: "/api/v1/users/me/certificate",
        summary: "Générer mon Certificat de Fiabilité Financière",
        tags: ["Utilisateurs"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Données du certificat",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "title", type: "string"),
                        new OA\Property(property: "user", type: "object"),
                        new OA\Property(property: "performance", type: "object"),
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
            )
        ]
    )]
    public function certificate(Request $request)
    {
        $user = $request->user();
        $totalCotise = $user->contributions()->where('status', 'confirmed')->count();
        $lateIncidents = \App\Models\Incident::where('user_id', $user->id)->count();

        return response()->json([
            'title' => 'Certificat de Crédit TontineChain',
            'user' => [
                'name' => $user->full_name,
                'npi_hash' => $user->npi_hash,
                'profession' => $user->profession,
            ],
            'performance' => [
                'score_confiance' => $user->score_confiance,
                'total_contributions_validated' => $totalCotise,
                'incident_rate' => $totalCotise > 0 ? round(($lateIncidents / $totalCotise) * 100, 2) : 0,
                'reliability_label' => $user->score_confiance >= 85 ? 'Excellente' : ($user->score_confiance >= 60 ? 'Bonne' : 'À surveiller'),
            ],
            'verification_link' => env('APP_URL') . "/verify/cert/" . $user->id,
            'timestamp' => now()->toDateTimeString(),
            'demo_notice' => [
                'is_simulation' => true,
                'message' => "MODÈLE DE SIMULATION : Ce certificat est généré dynamiquement et peut être présenté à des institutions partenaires pour obtenir des micro-crédits basés sur votre fiabilité dans TontineChain."
            ]
        ]);
    }

    #[OA\Get(
        path: "/api/v1/users/me/balance",
        summary: "Solde et statistiques financières de l'utilisateur",
        tags: ["Utilisateurs"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Détails financiers")
        ]
    )]
    public function balance(Request $request)
    {
        $user = $request->user();
        
        // Total déjà versé par l'utilisateur (confirmé)
        $totalCotise = $user->contributions()->where('status', 'confirmed')->sum('amount_fcfa');
        
        // Total déjà reçu par l'utilisateur (Payouts terminés)
        $totalRecu = $user->payouts()->where('status', 'completed')->sum('total_amount_fcfa');
        
        // Calcul du montant attendu pour les tontines en cours
        $expectedPayouts = \App\Models\Group::whereHas('members', function($q) use ($user) {
            $q->where('user_id', $user->id)->where('has_received', false)->where('status', 'active');
        })->get()->sum(function($group) {
            return $group->contribution_amount * $group->max_members;
        });

        return response()->json([
            'total_cotise_fcfa' => (int) $totalCotise,
            'total_recu_fcfa' => (int) $totalRecu,
            'expected_payouts_fcfa' => (int) $expectedPayouts,
            'currency' => 'XOF',
            'trust_score' => $user->score_confiance
        ]);
    }
}
