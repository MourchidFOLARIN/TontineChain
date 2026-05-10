<?php

namespace App\Http\Controllers;

use App\Models\TontineNotification;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TontineNotificationController extends Controller
{
    #[OA\Get(
        path: "/api/v1/notifications",
        summary: "Lister toutes les notifications",
        security: [["sanctum" => []]],
        tags: ["Notifications"],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Liste des notifications",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "notifications", type: "array", items: new OA\Items(type: "object")),
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
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = TontineNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'demo_notice' => [
                'is_simulation' => true,
                'message' => "MODÈLE DE SIMULATION : Ces notifications internes sont doublées d'envois automatiques par SMS, WhatsApp et Telegram pour garantir que l'utilisateur ne manque jamais une échéance ou un gain."
            ]
        ]);
    }

    #[OA\Patch(
        path: "/api/v1/notifications/{notification}/read",
        summary: "Marquer une notification comme lue",
        security: [["sanctum" => []]],
        tags: ["Notifications"],
        parameters: [
            new OA\PathParameter(name: "notification", required: true, description: "ID de la notification", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Notification mise à jour avec succès"),
            new OA\Response(response: 403, description: "Non autorisé")
        ]
    )]
    public function markRead(Request $request, TontineNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json($notification);
    }
}
