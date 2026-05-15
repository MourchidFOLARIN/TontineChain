<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: "/api/v1/users/me",
        summary: "Récupérer le profil complet de l'utilisateur",
        description: "Retourne toutes les informations personnelles, le score de confiance, le statut KYC et un message de bienvenue personnalisé (Audio + Texte) selon la langue choisie.",
        tags: ["Utilisateurs"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Détails du profil récupérés avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "full_name", type: "string", example: "Mourchid Olawale"),
                        new OA\Property(property: "email", type: "string", example: "user@example.com"),
                        new OA\Property(property: "phone", type: "string", example: "+22990000000"),
                        new OA\Property(property: "profession", type: "string", example: "Agriculteur"),
                        new OA\Property(property: "score_confiance", type: "integer", example: 100),
                        new OA\Property(property: "kyc_status", type: "string", example: "verified"),
                        new OA\Property(property: "preferred_language", type: "string", example: "fon"),
                        new OA\Property(property: "greetings", type: "object", properties: [
                            new OA\Property(property: "text", type: "string", example: "Bienvenue sur TontineChain"),
                            new OA\Property(property: "audio_url", type: "string", example: "https://.../welcome_fon.mp3")
                        ])
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
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
        summary: "Mettre à jour les informations du profil",
        description: "Permet de modifier le nom, la profession, le NPI (Numéro d'Identification Personnel) et la langue préférée.",
        tags: ["Utilisateurs"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Profil mis à jour",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Profil mis à jour avec succès"),
                        new OA\Property(property: "user", type: "object"),
                        new OA\Property(property: "demo_notice", type: "object")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Données invalides (ex: NPI déjà utilisé)")
        ]
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
            'user' => $user
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
            'user' => $user
        ]);
    }

    #[OA\Get(
        path: "/api/v1/users/me/score",
        summary: "Récupérer le score de confiance détaillé",
        description: "Retourne le score actuel (0-100) et la liste des incidents passés (retards, impayés) avec leur impact sur le score.",
        tags: ["Utilisateurs"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Score et historique récupérés",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "score_confiance", type: "integer", example: 85),
                        new OA\Property(property: "incidents", type: "array", items: new OA\Items(type: "object"))
                    ]
                )
            )
        ]
    )]
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
        summary: "Historique des gains reçus (Payouts)",
        description: "Liste tous les ramassages que l'utilisateur a déjà encaissés, avec les liens de preuve blockchain.",
        tags: ["Utilisateurs"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Tableau des ramassages")
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
        summary: "Classement des 10 membres les plus fiables",
        description: "Retourne les utilisateurs ayant les meilleurs scores de confiance sur la plateforme. Public.",
        tags: ["Utilisateurs"],
        responses: [
            new OA\Response(response: 200, description: "Top 10 récupéré")
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
        summary: "Générer les données du Certificat de Fiabilité",
        description: "Fournit les statistiques de performance pour générer un certificat de crédit (utile pour les partenaires financiers).",
        tags: ["Utilisateurs"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Données du certificat")
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
        summary: "Consulter le solde et les flux financiers",
        description: "Retourne le total cotisé, le total reçu et le montant attendu des tontines en cours.",
        tags: ["Utilisateurs"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Bilan financier",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "total_cotise_fcfa", type: "integer", example: 50000),
                        new OA\Property(property: "total_recu_fcfa", type: "integer", example: 150000),
                        new OA\Property(property: "expected_payouts_fcfa", type: "integer", example: 300000),
                        new OA\Property(property: "currency", type: "string", example: "XOF")
                    ]
                )
            )
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

    #[OA\Post(
        path: "/api/v1/users/me/kyc",
        summary: "Envoyer la pièce d'identité (Vérification KYC)",
        description: "Permet d'uploader une image (JPG/PNG) de la pièce d'identité. Déclenche une analyse OCR simulée par YAO.",
        tags: ["Utilisateurs"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Document reçu",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Document reçu avec succès"),
                        new OA\Property(property: "status", type: "string", example: "pending"),
                        new OA\Property(property: "document_url", type: "string")
                    ]
                )
            )
        ]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: "document", type: "string", format: "binary", description: "Image de la carte d'identité ou passeport")
                ]
            )
        )
    )]
    public function uploadKyc(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'document' => 'required|image|mimes:jpg,jpeg,png|max:5120', // 5MB max
        ]);

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('kyc_documents', 'public');
            
            $user->update([
                'id_card_path' => $path,
                'kyc_status' => 'pending'
            ]);

            // Simulation d'une analyse OCR intelligente
            return response()->json([
                'message' => 'Document reçu avec succès',
                'status' => 'pending',
                'document_url' => asset('storage/' . $path),
                'demo_notice' => [
                    'is_simulation' => true,
                    'message' => "ANALYSE OCR : YAO analyse votre pièce d'identité... Validité confirmée.",
                    'extracted_data' => [
                        'first_name' => $user->first_name ?: 'Mourchid',
                        'last_name' => $user->last_name ?: 'FOLARIN',
                        'npi' => '1234567890123', // NPI simulé à 13 chiffres
                        'confidence' => 0.998
                    ]
                ]
            ]);
        }

        return response()->json(['error' => 'Aucun document détecté'], 400);
    }
}
