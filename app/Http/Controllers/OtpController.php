<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class OtpController extends Controller
{
    protected $sms;

    public function __construct(SmsService $sms)
    {
        $this->sms = $sms;
    }

    #[OA\Post(
        path: "/api/v1/auth/request-otp",
        summary: "Demander un code OTP",
        tags: ["Authentification"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "phone", type: "string", example: "+22997000000")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "OTP envoyé")]
    #[OA\Response(response: 422, description: "Erreur de validation")]
    public function requestOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^\+?[0-9]{10,15}$/',
            'email' => 'required|email',
        ]);

        $phone = $this->sms->normalizePhone($request->phone);

        // Invalidate previous OTPs
        Otp::where('phone', $phone)->where('is_used', false)->update(['is_used' => true]);

        // Generate 6-digit code
        $code = (string) rand(100000, 999999);
        $codeHash = hash('sha256', $code);

        // Store OTP
        Otp::create([
            'phone' => $phone,
            'code_hash' => $codeHash,
            'expires_at' => Carbon::now()->addMinutes(5),
            'purpose' => 'login'
        ]);

        // Envoi par Email UNIQUEMENT (via Laravel Mail / SMTP pour flexibilité totale)
        if ($request->has('email')) {
            try {
                \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\OtpMail($code));
            } catch (\Exception $e) {
                Log::error("Erreur envoi Email OTP : " . $e->getMessage());
                return response()->json(['error' => "Erreur lors de l'envoi de l'email. Vérifiez vos réglages SMTP."], 500);
            }
        } else {
            return response()->json(['error' => "L'email est requis pour recevoir votre code de connexion."], 422);
        }

        // Toujours garder un log pour le dev
        Log::info("OTP for $phone: $code");
        
        return response()->json(['message' => 'OTP envoyé', 'phone' => $phone]);
    }

    #[OA\Post(
        path: "/api/v1/auth/verify-otp",
        summary: "Vérifier le code OTP et se connecter",
        tags: ["Authentification"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "phone", type: "string", example: "+22997000000"),
                new OA\Property(property: "code", type: "string", example: "123456")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Connexion réussie, retourne le token")]
    #[OA\Response(response: 401, description: "OTP invalide ou expiré")]
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $phone = $this->sms->normalizePhone($request->phone);
        $code = $request->code;
        $codeHash = hash('sha256', $code);

        $otp = Otp::where('phone', $phone)
            ->where('is_used', false)
            ->where('expires_at', '>', Carbon::now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otp) {
            return response()->json(['error' => 'OTP invalide ou expiré'], 401);
        }

        if ($otp->attempts >= 3) {
            return response()->json(['error' => 'Trop de tentatives'], 429);
        }

        if ($otp->code_hash !== $codeHash) {
            $otp->increment('attempts');
            return response()->json(['error' => 'Code incorrect'], 401);
        }

        // Mark as used
        $otp->update(['is_used' => true]);

        // Find or create user
        $user = User::where('phone', $phone)->first();
        $isNewUser = !$user;

        if (!$user) {
            $walletAddress = '0x' . Str::random(40); 
            
            $user = User::create([
                'phone' => $phone,
                'email' => $request->email ?? null,
                'full_name' => 'Membre',
                'wallet_address' => $walletAddress,
                'score_confiance' => 100,
                'kyc_status' => 'none',
                'is_active' => true,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'is_new_user' => $isNewUser
        ]);
    }
}
