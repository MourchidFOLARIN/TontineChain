<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $incidents = Incident::where('user_id', $user->id)
            ->orderBy('occurred_at', 'desc')
            ->get();

        return response()->json($incidents);
    }

    public function show(Request $request, Incident $incident)
    {
        if ($incident->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        return response()->json($incident);
    }
}
