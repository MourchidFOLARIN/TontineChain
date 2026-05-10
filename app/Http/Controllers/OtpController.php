<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
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
                new OA\Property(property: "phone", type: "string", example: "+22997000000"),
                new OA\Property(property: "email", type: "string", example: "user@example.com"),
                new OA\Property(property: "locale", type: "string", example: "yor", description: "Langue préférée (fr, yor, fon)")
            ]
        )
    )]
    #[OA\Response(
        response: 200, 
        description: "OTP envoyé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string"),
                new OA\Property(property: "message", type: "string"),
                new OA\Property(property: "phone", type: "string"),
                new OA\Property(
                    property: "demo_notice", 
                    type: "object",
                    properties: [
                        new OA\Property(property: "is_simulation", type: "boolean"),
                        new OA\Property(property: "otp_code", type: "integer"),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            ]
        )
    )]
    #[OA\Response(response: 422, description: "Erreur de validation")]
    public function requestOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'sometimes|string',
            'locale' => 'sometimes|string|in:fr,yor,fon'
        ]);

        // Gestion de la langue
        app()->setLocale($request->input('locale', 'fr'));

        $email = $request->email;
        $code = rand(100000, 999999);

        // On stocke l'OTP lié à l'email
        Otp::create([
            'email' => $email,
            'code_hash' => hash('sha256', $code),
            'expires_at' => \Carbon\Carbon::now()->addMinutes(15),
            'purpose' => 'login'
        ]);

        // --- ENVOI SMTP RÉEL ---
        Mail::to($email)->send(new OtpMail($code));

        // Toujours garder un log pour le dev
        Log::info("OTP for $email: $code");

        return response()->json([
            'status' => 'success',
            'message' => 'Le code OTP a été envoyé à votre adresse email.',
            'email' => $email,
            'demo_notice' => [
                'is_simulation' => true,
                'otp_code' => $code,
                'message' => "SIMULATION : Votre code est $code. Il a été envoyé par Email (SMTP). Pour le Hackathon, nous utilisons SMTP pour prouver l'envoi réel."
            ]
        ]);
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
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $email = $request->email;
        $code = $request->code;
        $codeHash = hash('sha256', $code);

        $otp = Otp::where('email', $email)
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

        // Find or create user by email
        $user = User::where('email', $email)->first();
        $isNewUser = !$user;

        if (!$user) {
            $walletAddress = '0x' . Str::random(40); 
            
            $user = User::create([
                'email' => $email,
                'full_name' => 'Membre',
                'wallet_address' => $walletAddress,
                'score_confiance' => 100,
                'kyc_status' => 'none',
                'preferred_language' => $request->input('locale', 'fr'),
                'is_active' => true,
            ]);
        } else {
            // Mettre à jour la langue si elle est précisée
            if ($request->has('locale')) {
                $user->update(['preferred_language' => $request->locale]);
            }
        }

        $needsProfileCompletion = empty($user->phone) || empty($user->first_name) || $user->full_name === 'Membre';

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'is_new_user' => $isNewUser,
            'needs_profile_completion' => $needsProfileCompletion,
            'demo_notice' => $needsProfileCompletion ? "NOUVEAU COMPTE: Le profil est incomplet. Le frontend doit maintenant demander les informations personnelles (Nom, Prénom, Téléphone) pour les futurs retraits." : null
        ]);
    }
}
