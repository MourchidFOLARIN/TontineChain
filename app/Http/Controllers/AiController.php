<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use App\Models\Contribution;
use App\Models\Payout;
use App\Models\Incident;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class AiController extends Controller
{
    #[OA\Post(
        path: "/api/v1/ai/chat",
        summary: "Converser avec YAO (Assistant Intelligent)",
        tags: ["IA"],
        security: [["sanctum" => []]]
    )]
    public function chat(Request $request)
    {
        $user = $request->user();
        $message = strtolower($request->input('message', ''));
        $locale = $request->input('locale', 'fr');
        
        // --- ANALYSE DE LA BASE DE DONNÉES ---
        $memberships = $user->memberships()->with('group.members.user')->get();
        $totalPaid = Contribution::where('user_id', $user->id)->where('status', 'confirmed')->sum('amount_fcfa');
        $incidentsCount = Incident::where('user_id', $user->id)->count();
        $activeGroup = $memberships->where('group.status', 'active')->first();
        
        // --- DÉTECTION DES MOTS-CLÉS (FR, YOR, FON) ---
        
        // Identité / Profil
        $isIdentity = str_contains($message, 'qui suis-je') || str_contains($message, 'mon profil') || 
                      str_contains($message, 'mè wi') || str_contains($message, 'tani emi');

        // Argent / Bilan
        $isMoney = str_contains($message, 'argent') || str_contains($message, 'payé') || 
                   str_contains($message, 'bilan') || str_contains($message, 'akwé') || 
                   str_contains($message, 'owo');

        // Dates / Échéances
        $isDate = str_contains($message, 'quand') || str_contains($message, 'date') || 
                  str_contains($message, 'prochain') || str_contains($message, 'hwenu') || 
                  str_contains($message, 'igba wo');

        // Confiance / Score
        $isTrust = str_contains($message, 'score') || str_contains($message, 'confiance') || 
                   str_contains($message, 'jiɖe') || str_contains($message, 'igbekele');

        // Blockchain / Sécurité
        $isTech = str_contains($message, 'blockchain') || str_contains($message, 'sécurité') || 
                  str_contains($message, 'comment') || str_contains($message, 'tontine');

        // --- GÉNÉRATION DE LA RÉPONSE ---
        $response = "";

        if ($isIdentity) {
            $response = "Tu es " . $user->full_name . ". Je te connais bien ! Tu es avec nous depuis le " . $user->created_at->format('d/m/Y') . ".";
        } 
        elseif ($isMoney) {
            $response = "Ton bilan financier est de " . number_format($totalPaid, 0, ',', ' ') . " FCFA versés. ";
            if ($incidentsCount > 0) {
                $response .= "Attention, j'ai remarqué " . $incidentsCount . " incident(s) de retard. Essaie d'être plus ponctuel pour ton score.";
            } else {
                $response .= "Ton historique est impeccable, aucune fausse note !";
            }
        }
        elseif ($isDate) {
            if ($activeGroup && $activeGroup->group->next_due_date) {
                $response = "Ta prochaine cotisation pour '" . $activeGroup->group->name . "' est attendue le " . Carbon::parse($activeGroup->group->next_due_date)->format('d/m/Y') . ".";
            } else {
                $response = "Tu n'as aucune échéance de paiement pour le moment. C'est le calme plat !";
            }
        }
        elseif ($isTrust) {
            $response = "Ton score est de " . $user->score_confiance . "/100. ";
            if ($user->score_confiance < 60) {
                $response .= "Mon conseil : paie tes 3 prochaines cotisations avant la date limite pour gagner +15 points rapidement.";
            } else {
                $response .= "Tu es un pilier de la communauté. Continue comme ça !";
            }
        }
        elseif ($isTech) {
            $response = "TontineChain utilise la Blockchain Polygon pour garantir que personne ne peut voler l'argent du groupe. Chaque transaction est publique et vérifiable. C'est la transparence totale !";
        }
        else {
            $response = "Je n'ai pas bien saisi, mais je peux t'aider sur : ton bilan financier (akwé/owo), tes prochaines dates (hwenu/igba), ton score de confiance (jiɖe) ou le fonctionnement de la blockchain.";
        }

        return response()->json([
            'assistant' => 'YAO',
            'message' => $response,
            'audio_url' => null, 
            'demo_notice' => [
                'is_simulation' => true,
                'message' => "MULTILINGUAL AI : YAO a détecté des mots-clés en " . ($locale === 'fr' ? 'Français' : 'Langue Locale') . " et a analysé l'historique des incidents et des paiements pour répondre."
            ]
        ]);
    }
}
