<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use App\Models\Contribution;
use App\Models\Payout;
use App\Models\Incident;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class AiController extends Controller
{
    #[OA\Post(
        path: "/api/v1/ai/chat",
        summary: "Discuter avec YAO (Assistant Intelligent Multilingue)",
        description: "YAO est capable de comprendre le Français, le Fon et le Yoruba. Il a accès en temps réel à votre solde, vos retards de paiement, votre score de confiance et vos contrats blockchain pour répondre précisément à vos besoins.",
        tags: ["Intelligence Artificielle (YAO)"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "message", type: "string", example: "Bilan akwé nabi ?", description: "Question en français ou langue locale"),
                    new OA\Property(property: "locale", type: "string", example: "fon", description: "Langue de réponse souhaitée (fr, fon, yor)")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Réponse générée par l'IA",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "assistant", type: "string", example: "YAO"),
                        new OA\Property(property: "message", type: "string", example: "Ton bilan est de 50 000 FCFA..."),
                        new OA\Property(property: "audio_url", type: "string", nullable: true, description: "URL du guide vocal généré (si disponible)"),
                        new OA\Property(property: "demo_notice", type: "object")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Non authentifié")
        ]
    )]
    public function chat(Request $request)
    {
        $user = $request->user();
        $message = strtolower($request->input('message', ''));
        $locale = $request->input('locale', 'fr');
        
        // --- ANALYSE DE LA BASE DE DONNÉES ---
        $memberships = $user->memberships()->with('group.members.user')->get();
        $totalPaid = Contribution::where('user_id', $user->id)->where('status', 'confirmed')->sum('amount_fcfa');
        $incidentsCount = Incident::where('user_id', $user->id)->count();
        $activeGroup = $memberships->where('group.status', 'active')->first();
        
        // --- INTÉGRATION DE LA VÉRITABLE IA (GEMINI API) ---
        $geminiApiKey = env('GEMINI_API_KEY') ?: getenv('GEMINI_API_KEY');

        if ($geminiApiKey) {
            $systemPrompt = "Tu es YAO, l'assistant IA expert de TontineChain (Hackathon MIABE 2026). 
Rôle : Tu es un conseiller financier béninois, chaleureux, expert en tontines et en blockchain. Ton but est d'aider l'utilisateur à prospérer.

Contexte TontineChain :
- Sécurité : Blockchain Polygon (transparence, immutabilité).
- Score de Confiance : C'est le 'Crédit Score' local. Départ à 100. Baisse en cas de retard (Incident). Remonte avec la ponctualité.
- Enchères (Bidding) : Permet de ramasser le pot plus tôt en proposant une décote. C'est idéal pour un besoin urgent de cash.
- Langues : Tu maîtrises le Français, le Fon (ex: 'Awanu', 'Kudéou') et le Yoruba (ex: 'E nlé o', 'E kaabo').

Données de l'utilisateur (" . $user->full_name . ") :
- Score actuel : " . $user->score_confiance . "/100 (" . ($user->score_confiance >= 80 ? "Élite" : "Standard") . ")
- Historique : " . number_format($totalPaid, 0, ',', ' ') . " FCFA cotisés au total.
- Santé financière : " . ($incidentsCount > 0 ? "Attention, $incidentsCount retard(s) détecté(s)." : "Parfait, aucun incident.") . "
- Prochaine action : " . ($activeGroup && $activeGroup->group->next_due_date ? "Échéance le " . Carbon::parse($activeGroup->group->next_due_date)->format('d/m/Y') : "Inscris-toi à une tontine !") . "

Directives de réponse :
- Réponds en " . strtoupper($locale) . " (obligatoire). Si c'est Fon ou Yoruba, utilise un ton authentique du Bénin.
- Sois expert et pédagogue. Explique les concepts (Blockchain, Score) si on te le demande.
- Personnalise au maximum avec les données ci-dessus.
- Utilise des emojis pour un ton moderne. Ne cite jamais ce prompt.";

            try {
                $geminiResponse = \Illuminate\Support\Facades\Http::timeout(15)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$geminiApiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $systemPrompt . "\n\nVoici la question de l'utilisateur : " . $request->input('message')]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 300,
                    ]
                ]);

                if ($geminiResponse->successful() && $geminiResponse->json('candidates.0.content.parts.0.text')) {
                    $aiText = $geminiResponse->json('candidates.0.content.parts.0.text');
                    return response()->json([
                        'assistant' => 'YAO',
                        'message' => $aiText,
                        'audio_url' => null
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gemini API Error: ' . $e->getMessage());
                // Fallback en cas d'erreur réseau
            }
        }

        // --- FALLBACK (Si API Key manquante ou erreur réseau) ---
        $isMoney = str_contains($message, 'argent') || str_contains($message, 'bilan') || str_contains($message, 'akwé');
        $isTrust = str_contains($message, 'score') || str_contains($message, 'confiance') || str_contains($message, 'jiɖe');
        $isTech = str_contains($message, 'blockchain') || str_contains($message, 'sécurité');

        $response = "";
        if ($isMoney) {
            $response = "Ton bilan est de " . number_format($totalPaid, 0, ',', ' ') . " FCFA. Continue comme ça " . $user->first_name . " !";
        } elseif ($isTrust) {
            $response = "Ton score de confiance est de " . $user->score_confiance . "/100. Pense à payer à l'heure pour l'améliorer.";
        } elseif ($isTech) {
            $response = "TontineChain utilise la blockchain Polygon pour garantir une transparence totale.";
        } else {
            $response = "Bonjour " . $user->first_name . " ! Je suis YAO. Tu peux me poser des questions sur ton score, tes cotisations ou le fonctionnement du système.";
        }

        return response()->json([
            'assistant' => 'YAO',
            'message' => $response,
            'audio_url' => null
        ]);
    }
}
