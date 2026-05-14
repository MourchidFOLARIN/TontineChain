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
        summary: "Liste des messages du groupe",
        tags: ["Messagerie"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Liste des messages du groupe",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(
                type: "object",
                properties: [
                    new OA\Property(property: "id", type: "integer", example: 1),
                    new OA\Property(property: "content", type: "string", example: "Bonjour à tous"),
                    new OA\Property(property: "user", type: "object", properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "full_name", type: "string", example: "Jean Dupont")
                    ])
                ]
            )
        )
    )]
    #[OA\Response(response: 403, description: "Non autorisé")]
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
        summary: "Envoyer un message dans le groupe",
        tags: ["Messagerie"],
        security: [["sanctum" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "content", type: "string", example: "Bonjour à tous"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Message créé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "id", type: "integer", example: 1),
                new OA\Property(property: "content", type: "string", example: "Bonjour à tous"),
                new OA\Property(property: "user", type: "object", properties: [
                    new OA\Property(property: "id", type: "integer", example: 1),
                    new OA\Property(property: "full_name", type: "string", example: "Jean Dupont")
                ])
            ]
        )
    )]
    #[OA\Response(response: 403, description: "Non autorisé")]
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
