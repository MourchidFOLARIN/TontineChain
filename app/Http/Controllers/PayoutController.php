<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $payouts = Payout::where('beneficiary_id', $user->id)->get();

        return response()->json($payouts);
    }

    public function show(Request $request, Payout $payout)
    {
        if ($payout->beneficiary_id !== $request->user()->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        return response()->json($payout);
    }
}
