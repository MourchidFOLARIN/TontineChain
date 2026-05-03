<?php

namespace App\Http\Controllers;

use App\Models\TontineNotification;
use Illuminate\Http\Request;

class TontineNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = TontineNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    public function markRead(Request $request, TontineNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json($notification);
    }
}
