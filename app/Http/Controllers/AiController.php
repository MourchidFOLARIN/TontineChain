<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use App\Models\Contribution;
use App\Models\Payout;
use App\Models\Incident;
use App\Services\YaoIntelligenceService;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class AiController extends Controller
{
    public function chat(Request $request)
    {
        return response()->json([
            'assistant' => 'YAO',
            'message' => 'Test statique : YAO est en ligne et prêt à vous aider.',
            'audio_url' => null,
            'demo_notice' => [
                'mode' => 'static_test',
                'status' => 'active'
            ]
        ]);
    }
}
