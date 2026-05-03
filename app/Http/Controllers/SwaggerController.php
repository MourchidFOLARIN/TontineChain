<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(title: "TontineChain API", version: "1.0.0", description: "Documentation de l'API TontineChain pour le Hackathon MIABE 2026.")]
#[OA\Server(url: "http://localhost:8000", description: "Serveur Local")]
class SwaggerController extends Controller
{
    public function index()
    {
        return redirect('/api/documentation');
    }
}
