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
    public function index(Group $group)
    {
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
    public function store(Request $request, Group $group)
    {
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
}
