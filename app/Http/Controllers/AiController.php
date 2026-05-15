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
            $systemPrompt = "Tu es YAO, l'assistant IA officiel de la plateforme TontineChain (créée pour le Hackathon MIABE 2026 au Bénin). Tu es un conseiller financier expert, empathique et multilingue.
Règles de TontineChain :
- C'est une tontine numérique sécurisée par la blockchain Polygon pour la transparence.
- L'utilisateur a un 'Score de Confiance' (sur 100). Au-dessus de 80, il est dans l'élite. S'il a des incidents de retard, son score baisse.
- Les enchères (bidding) : un membre peut proposer une 'décote' pour ramasser le pot en avance. La décote est partagée avec les autres membres.
- L'assurance : une petite partie des gains va dans une caisse de secours.

Informations en temps réel sur l'utilisateur avec qui tu parles :
- Nom : " . $user->full_name . "
- Score de confiance : " . $user->score_confiance . "/100
- Total cotisé : " . number_format($totalPaid, 0, ',', ' ') . " FCFA
- Retards enregistrés : " . $incidentsCount . "
- Prochaine échéance : " . ($activeGroup && $activeGroup->group->next_due_date ? Carbon::parse($activeGroup->group->next_due_date)->format('d/m/Y') : "Aucune tontine active") . "

Directives strictes pour ta réponse :
- Tu dois impérativement répondre dans la langue demandée : " . strtoupper($locale) . " (fr = Français, fon = Fon du Bénin, yor = Yoruba).
- Sois très chaleureux, concis (pas plus de 4 phrases) et utilise des emojis.
- Utilise ses informations pour personnaliser la réponse. Ne dis jamais que tu es un modèle de langage.";

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
