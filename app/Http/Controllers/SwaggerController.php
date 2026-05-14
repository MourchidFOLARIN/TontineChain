<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(title: "TontineChain API", version: "1.0.0", description: "Documentation de l'API TontineChain pour le Hackathon MIABE 2026.")]
#[OA\Server(url: "http://localhost:8000", description: "Serveur Local")]
#[OA\SecurityScheme(
    securityScheme: "BearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Utiliser le token Bearer retourné par /api/v1/auth/verify-otp"
)]
class SwaggerController extends Controller
{
    public function index()
    {
        return redirect('/api/documentation');
    }
}
