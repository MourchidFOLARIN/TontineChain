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
    public function chat(Request $request, \App\Services\YaoIntelligenceService $yaoService)
    {
        $user = $request->user();
        $message = $request->input('message', '');
        $locale = $request->input('locale', 'fr');
        
        try {
            $responseMessage = $yaoService->generateResponse($user, $message, $locale);
            
            return response()->json([
                'assistant' => 'YAO',
                'message' => $responseMessage,
                'audio_url' => null,
                'demo_notice' => [
                    'mode' => 'local_intelligence',
                    'status' => 'active',
                    'engine' => 'YAO-Scraper-v1'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'assistant' => 'YAO-ERROR',
                'message' => 'Oups ! J\'ai eu un petit problème technique en consultant tes données. Réessaie dans un instant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
