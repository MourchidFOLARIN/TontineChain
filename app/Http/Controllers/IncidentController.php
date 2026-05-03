<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class IncidentController extends Controller
{
    #[OA\Get(
        path: "/api/v1/incidents",
        summary: "Lister les incidents (retards, amendes)",
        security: [["sanctum" => []]],
        tags: ["Incidents"],
        responses: [
            new OA\Response(response: 200, description: "Liste des incidents de l'utilisateur")
        ]
    )]
    public function index(Request $request)
    {
        $user = $request->user();
        $incidents = Incident::where('user_id', $user->id)
            ->orderBy('occurred_at', 'desc')
            ->get();

        return response()->json($incidents);
    }

    #[OA\Get(
        path: "/api/v1/incidents/{incident}",
        summary: "Détails d'un incident spécifique",
        security: [["sanctum" => []]],
        tags: ["Incidents"],
        parameters: [
            new OA\PathParameter(name: "incident", required: true, description: "ID de l'incident", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Détails de l'incident"),
            new OA\Response(response: 403, description: "Non autorisé")
        ]
    )]
    public function show(Request $request, Incident $incident)
    {
        if ($incident->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        return response()->json($incident);
    }
}
