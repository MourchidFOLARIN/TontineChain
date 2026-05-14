<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use OpenApi\Attributes as OA;
use Throwable;

class OtpController extends Controller
{
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
        description: "OTP envoyé (le code est uniquement dans l’email, sauf mode local debug)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string"),
                new OA\Property(property: "message", type: "string"),
                new OA\Property(property: "email", type: "string"),
            ]
        )
    )]
    #[OA\Response(response: 422, description: "Erreur de validation")]
    public function requestOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'sometimes|string',
            'locale' => 'sometimes|string|in:fr,yor,fon',
        ]);

        app()->setLocale($request->input('locale', 'fr'));

        $email = strtolower(trim($request->email));
        $code = (string) random_int(100000, 999999);

        $otpRecord = Otp::create([
            'email' => $email,
            'code_hash' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(15),
            'purpose' => 'login',
        ]);

        try {
            Mail::to($email)->send(new OtpMail($code));
        } catch (Throwable $e) {
            $otpRecord->delete();
            Log::error("Erreur d'envoi OTP pour {$email}", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $message = config('app.debug')
                ? 'Erreur lors de l\'envoi du code OTP : '.$e->getMessage()
                : 'Impossible d\'envoyer l\'email. Vérifiez MAIL_MAILER=smtp et vos identifiants SMTP (MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS).';

            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 500);
        }

        if (app()->environment('local') && config('app.debug')) {
            Log::debug('OTP email envoyé', ['email' => $email]);
        }

        $payload = [
            'status' => 'success',
            'message' => 'Le code OTP a été envoyé à votre adresse email. Consultez votre boîte de réception (et les courriers indésirables).',
            'email' => $email,
        ];

        if (app()->environment('local') && config('app.debug')) {
            $payload['demo_notice'] = [
                'is_simulation' => true,
                'otp_code' => $code,
                'message' => 'Mode local + APP_DEBUG : le code est aussi affiché ici pour les tests sans boîte mail. Ne jamais activer en production.',
            ];
        }

        return response()->json($payload);
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
                new OA\Property(property: "email", type: "string", example: "user@example.com"),
                new OA\Property(property: "code", type: "string", example: "123456")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Connexion réussie, retourne le token",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "access_token", type: "string", example: "1|abcdef1234567890"),
                new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                new OA\Property(property: "user", type: "object", description: "Utilisateur connecté", properties: [
                    new OA\Property(property: "id", type: "integer", example: 1),
                    new OA\Property(property: "email", type: "string", example: "user@example.com"),
                    new OA\Property(property: "full_name", type: "string", example: "Membre"),
                    new OA\Property(property: "phone", type: "string", nullable: true, example: null),
                ]),
                new OA\Property(property: "needs_profile_completion", type: "boolean", example: true),
            ]
        )
    )]
    #[OA\Response(response: 401, description: "OTP invalide ou expiré")]
    #[OA\Response(response: 500, description: "Erreur technique")]
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $email = strtolower(trim($request->email));
        $code = $request->code;
        $codeHash = hash('sha256', $code);

        if (app()->environment('local') && config('app.debug')) {
            Log::debug('Tentative de vérification OTP', ['email' => $email]);
        }

        $otp = Otp::where('email', $email)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $otp) {
            Log::warning("OTP not found or expired for {$email}");

            return response()->json(['error' => 'OTP invalide ou expiré'], 401);
        }

        if ($otp->attempts >= 3) {
            return response()->json(['error' => 'Trop de tentatives'], 429);
        }

        if ($otp->code_hash !== $codeHash) {
            $otp->increment('attempts');

            return response()->json(['error' => 'Code incorrect'], 401);
        }

        $otp->update(['is_used' => true]);

        try {
            $user = User::where('email', $email)->first();

            if (! $user) {
                Log::info("Creating new user for {$email}");
                $user = User::create([
                    'email' => $email,
                    'full_name' => 'Membre',
                    'is_active' => true,
                    'preferred_language' => app()->getLocale(),
                    'wallet_address' => '0x'.\Illuminate\Support\Str::random(40),
                    'phone' => null,
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                'needs_profile_completion' => empty($user->first_name) || empty($user->phone),
            ]);
        } catch (Throwable $e) {
            Log::error('Error in verifyOtp: '.$e->getMessage(), ['exception' => $e]);

            return response()->json(['error' => 'Erreur technique'], 500);
        }
    }
}
