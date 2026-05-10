<?php

use App\Http\Controllers\OtpController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContributionController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\TontineNotificationController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SwaggerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Health check pour Render
    Route::get('/health', function () {
        return response()->json(['status' => 'ok', 'message' => 'Service is healthy'], 200);
    });

    // Dummy login route to prevent 500 errors on unauthenticated API calls
    Route::get('/login', function () {
        return response()->json(['error' => 'Unauthenticated'], 401);
    })->name('login');

    // Debug & Connection Tests
    Route::get('/debug/fedapay', [\App\Http\Controllers\DebugController::class, 'testFedapay']);
    Route::get('/debug/infobip', [\App\Http\Controllers\DebugController::class, 'testInfobip']);
    Route::get('/debug/blockchain', [\App\Http\Controllers\DebugController::class, 'testBlockchain']);
    Route::get('/debug/telegram', [\App\Http\Controllers\DebugController::class, 'testTelegram']);
    Route::get('/debug/email', [\App\Http\Controllers\DebugController::class, 'testEmail']);

    // Auth Routes
    Route::post('/auth/request-otp', [OtpController::class, 'requestOtp']);
    Route::post('/auth/verify-otp', [OtpController::class, 'verifyOtp']);

    // Public Routes
    Route::get('/users/leaderboard', [UserController::class, 'leaderboard']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/users/me', [UserController::class, 'me']);
        Route::patch('/users/me', [UserController::class, 'update']);
        Route::post('/user/profile', [UserController::class, 'profile']);
        Route::get('/users/me/score', [UserController::class, 'score']);
        Route::get('/users/me/payouts', [UserController::class, 'payouts']);
        Route::get('/users/me/balance', [UserController::class, 'balance']);
        
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Group Routes
        Route::post('/groups', [GroupController::class, 'store']);
        Route::get('/groups', [GroupController::class, 'index']);
        Route::get('/groups/{group}', [GroupController::class, 'show']);
        Route::post('/groups/{group}/invite', [GroupController::class, 'invite']);
        Route::post('/groups/{group}/join', [GroupController::class, 'join']);
        Route::post('/groups/{group}/start', [GroupController::class, 'start']);
        Route::get('/groups/{group}/stats', [GroupController::class, 'stats']);
        Route::get('/groups/{group}/messages', [MessageController::class, 'index']);
        Route::post('/groups/{group}/messages', [MessageController::class, 'store']);
        Route::get('/groups/{group}/contract', [GroupController::class, 'downloadContract']);
        Route::post('/groups/{group}/propose-swap', [VoteController::class, 'proposeSwap']);

        // Contribution Routes
        Route::get('/contributions/pending', [ContributionController::class, 'pending']);
        Route::post('/contributions/{contribution}/pay', [ContributionController::class, 'initiate']);

        Route::get('/payouts', [PayoutController::class, 'index']);
        Route::get('/payouts/{payout}', [PayoutController::class, 'show']);

        Route::get('/incidents', [IncidentController::class, 'index']);
        Route::get('/incidents/{incident}', [IncidentController::class, 'show']);

        Route::get('/notifications', [TontineNotificationController::class, 'index']);
        Route::patch('/notifications/{notification}/read', [TontineNotificationController::class, 'markRead']);

        Route::get('/votes', [VoteController::class, 'index']);
        Route::get('/votes/{vote}', [VoteController::class, 'show']);
        Route::post('/votes/{vote}/cast', [VoteController::class, 'castVote']);

        // AI Assistant (YAO)
        Route::post('/ai/chat', [AiController::class, 'chat']);
    });

    // Webhooks
    Route::post('/webhooks/fedapay', [WebhookController::class, 'handleFedapay']);

    Route::get('/docs', [SwaggerController::class, 'index']);
});
