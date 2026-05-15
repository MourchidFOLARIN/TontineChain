<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Group;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MessageController extends Controller
{
    #[OA\Get(
        path: "/api/v1/groups/{group}/messages",
        summary: "Récupérer l'historique de discussion du groupe",
        description: "Retourne tous les messages échangés dans la tontine. Inclut les messages envoyés par les membres et les alertes automatiques du système (ex: confirmation de paiement, incidents).",
        tags: ["Messagerie Sociale"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "group", in: "path", required: true, description: "ID du groupe", schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Historique des messages récupéré",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "content", type: "string", example: "Le contrat intelligent a été déployé !"),
                            new OA\Property(property: "is_system", type: "boolean", example: true),
                            new OA\Property(property: "user", type: "object", nullable: true, properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "full_name", type: "string", example: "Jean Dupont")
                            ])
                        ]
                    )
                )
            ),
            new OA\Response(response: 403, description: "Vous n'êtes pas membre actif de ce groupe")
        ]
    )]
    public function index(Request $request, Group $group)
    {
        if (! $this->userCanMessageGroup($request, $group)) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $messages = $group->messages()
            ->with('user:id,full_name')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    #[OA\Post(
        path: "/api/v1/groups/{group}/messages",
        summary: "Envoyer un nouveau message au groupe",
        description: "Permet aux membres actifs de discuter entre eux.",
        tags: ["Messagerie Sociale"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "group", in: "path", required: true, description: "ID du groupe", schema: new OA\Schema(type: "string"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "content", type: "string", example: "Est-ce que tout le monde a reçu son PDF de contrat ?"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Message envoyé avec succès"),
            new OA\Response(response: 403, description: "Action non autorisée")
        ]
    )]
    public function store(Request $request, Group $group)
    {
        if (! $this->userCanMessageGroup($request, $group)) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'group_id' => $group->id,
            'user_id' => $request->user()->id,
            'content' => $request->content,
            'is_system' => false,
        ]);

        return response()->json($message->load('user:id,full_name'), 201);
    }

    /**
     * Helper pour envoyer un message système (utilisé par les autres contrôleurs)
     */
    public static function sendSystemMessage($groupId, $content)
    {
        return Message::create([
            'group_id' => $groupId,
            'user_id' => null,
            'content' => $content,
            'is_system' => true,
        ]);
    }

    private function userCanMessageGroup(Request $request, Group $group): bool
    {
        return $group->status === 'active'
            && $group->members()
                ->where('user_id', $request->user()->id)
                ->where('status', 'active')
                ->exists();
    }
}
