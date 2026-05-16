<?php

namespace App\Services;

use App\Models\User;
use App\Models\Group;
use App\Models\Contribution;
use App\Models\Payout;
use App\Models\Incident;
use Carbon\Carbon;

class YaoIntelligenceService
{
    protected $user;
    protected $locale;
    protected $message;

    public function generateResponse(User $user, string $message, string $locale = 'fr')
    {
        $this->user = $user;
        $this->message = mb_strtolower($message);
        $this->locale = $locale;

        $intent = $this->detectIntent();
        $data = $this->scrapeData($intent);

        return $this->formatResponse($intent, $data);
    }

    protected function detectIntent()
    {
        $keywords = [
            'MONEY' => ['argent', 'bilan', 'solde', 'payé', 'cotisé', 'akwé', 'kwé', 'owó'],
            'TRUST' => ['score', 'confiance', 'jiɖe', 'itẹmọlẹ', 'fiable', 'crédit'],
            'GROUPS' => ['tontine', 'groupe', 'échéance', 'date', 'quand', 'prochaine', 'azán', 'ìgbà'],
            'RANKING' => ['rang', 'classement', 'meilleur', 'position', 'tuto', 'lɛ́ndí'],
            'BLOCKCHAIN' => ['blockchain', 'sécurité', 'wallet', 'contrat', 'adresse', 'polygon', 'hash'],
            'INSURANCE' => ['assurance', 'sécurité', 'caisse', 'secours', 'fond', 'alɔgɔ'],
            'MEMBERS' => ['membres', 'amis', 'gens', 'qui est avec moi', 'mɛ ɖetɔ́'],
            'IDENTITY' => ['qui es-tu', 'yao', 'quel est ton nom', 'mɛ mɛ wɛ nyi we', 'ta ni ọ'],
            'HELP' => ['aide', 'comment', 'fonctionne', 'marcher', 'alọgọ', 'ìrànlọ́wọ́', 'help', 'aider'],
            'GREETING' => ['bonjour', 'salut', 'coucou', 'kúàbọ̀', 'ndé', 'kú', 'hello', 'hey'],
        ];

        foreach ($keywords as $intent => $words) {
            foreach ($words as $word) {
                if (str_contains($this->message, $word)) {
                    return $intent;
                }
            }
        }

        return 'GENERAL';
    }

    protected function scrapeData($intent)
    {
        $data = [
            'total_paid' => 0,
            'incidents_count' => 0,
            'payouts_received' => 0,
        ];

        // Scrape basic user info always
        $data['first_name'] = $this->user->first_name ?: ($this->user->full_name ?: 'Cher membre');
        $data['score'] = $this->user->score_confiance;
        $data['wallet_address'] = $this->user->wallet_address;
        
        // Stats for Ranking
        $data['rank'] = User::where('score_confiance', '>', $this->user->score_confiance)->count() + 1;
        $data['total_users'] = User::count();

        // Common group data
        $membership = $this->user->memberships()->with(['group', 'group.members.user'])->whereHas('group', function($q) {
            $q->where('status', 'active');
        })->first();
        $data['active_group'] = $membership ? $membership->group : null;

        switch ($intent) {
            case 'MONEY':
                $data['total_paid'] = Contribution::where('user_id', $this->user->id)->where('status', 'confirmed')->sum('amount_fcfa');
                break;

            case 'TRUST':
                $data['incidents_count'] = Incident::where('user_id', $this->user->id)->count();
                $data['payouts_received'] = Payout::where('beneficiary_id', $this->user->id)->count();
                break;
        }

        return $data;
    }

    protected function formatResponse($intent, $data)
    {
        $responses = [
            'fr' => [
                'GREETING' => "Bonjour {$data['first_name']} ! Je suis YAO, ton assistant TontineChain. Comment puis-je t'aider aujourd'hui ? 😊",
                'IDENTITY' => "Je suis YAO, l'IA locale de TontineChain. Je suis là pour t'aider à gérer tes finances et suivre tes tontines en toute sécurité. 🤖",
                'MONEY' => "Tu as cotisé un total de " . number_format($data['total_paid'] ?? 0, 0, ',', ' ') . " FCFA sur la plateforme. C'est un excellent début ! 💰",
                'TRUST' => "Ton score de confiance est de {$data['score']}/100. " . ($data['score'] > 80 ? "Tu es un membre d'élite ! ⭐" : "Continue à payer tes cotisations à temps pour l'améliorer."),
                'GROUPS' => isset($data['active_group']) ? "Ta prochaine échéance pour le groupe '{$data['active_group']->name}' est le " . Carbon::parse($data['active_group']->next_due_date)->format('d/m/Y') . ". N'oublie pas ! 📅" : "Tu n'as pas de tontine active pour le moment.",
                'RANKING' => "Tu es classé {$data['rank']}ème sur {$data['total_users']} utilisateurs. " . ($data['rank'] == 1 ? "Tu es le champion ! 🏆" : "Continue comme ça pour monter au sommet !"),
                'BLOCKCHAIN' => "Tes transactions sont sécurisées sur la blockchain Polygon. Ton adresse wallet est : " . ($data['wallet_address'] ?: "Non configurée") . ". 🔒",
                'INSURANCE' => isset($data['active_group']) ? "Le fonds d'assurance de ton groupe '{$data['active_group']->name}' est de " . number_format($data['active_group']->insurance_fund, 0, ',', ' ') . " FCFA. Il sert en cas d'imprévu. 🛡️" : "L'assurance TontineChain te protège contre les impayés et les coups durs.",
                'MEMBERS' => isset($data['active_group']) ? "Dans ton groupe, il y a " . $data['active_group']->members->count() . " membres, dont " . implode(', ', $data['active_group']->members->pluck('user.first_name')->filter()->take(3)->toArray()) . "... 👥" : "Rejoins un groupe pour voir tes futurs partenaires de tontine !",
                'HELP' => "Tu peux me poser des questions sur ton solde, ton score, ton rang, tes membres ou la sécurité blockchain ! 💡",
                'GENERAL' => "Je n'ai pas bien compris, mais je suis là pour toi. Tu peux me demander ton solde ou ton score de confiance par exemple. 👋"
            ],
            'fon' => [
                'GREETING' => "Kúàbọ̀ {$data['first_name']} ! YAO wɛ nyí mì. Nɛ nà d'alɔ wè gbɔn ? 😊",
                'IDENTITY' => "YAO wɛ nyí mì, mɔ̌ tɔn TontineChain tɔn. N'ɖò fì bo nà d'alɔ wè dó akwé towe kplé jí. 🤖",
                'MONEY' => "Akwé nàbí e a kplé é bǐ d'asú " . number_format($data['total_paid'] ?? 0, 0, ',', ' ') . " FCFA. É nyɔ́ tawun ! 💰",
                'TRUST' => "Jiɖe e mǐ ɖó dó wè é sù sɔ {$data['score']}/100. Kpo ɖò akwé towe sú wɛ ɖò ganmɛ. ⭐",
                'GROUPS' => isset($data['active_group']) ? "Azán e gbè a nà sú akwé ɖò kplé '{$data['active_group']->name}' tɔn mɛ é wɛ nyí " . Carbon::parse($data['active_group']->next_due_date)->format('d/m/Y') . ". Ma wɔn ó ! 📅" : "A ɖò kplé ɖě mɛ din ǎ.",
                'RANKING' => "A ɖò mɛ {$data['rank']} gɔ́ jí ɖò mɛ {$data['total_users']} lɛ mɛ. Gudo à ! 🏆",
                'BLOCKCHAIN' => "Akwé towe ɖò fì e è nà jɛ gudo tɔn ǎ é, ɖò blockchain Polygon jí. 🔒",
                'INSURANCE' => "Akwé e ɖò cɛ́kɛ́ mɛ bo nà d'alɔ mɛ é sù tawun. 🛡️",
                'MEMBERS' => "Mɛ e ɖò kplé towe mɛ lɛ é sù tawun. 👥",
                'HELP' => "A hɛn bo nà kan nǔbyɔ́ mì dó akwé towe jí, aló dó kplé e mɛ a ɖè é jí. 💡",
                'GENERAL' => "N'mɔ nǔ jɛ nǔ e a ɖɔ é mɛ ganjí ǎ, loǒ ɔ n'ɖò fì nà d'alɔ wè. 👋"
            ],
            'yor' => [
                'GREETING' => "Ẹ kú àbọ̀ {$data['first_name']} ! Èmi ni YAO. Báwo ni mo ṣe lè ràn ọ́ lọ́wọ́ ? 😊",
                'IDENTITY' => "Èmi ni YAO, olùrànlọ́wọ́ rẹ ní TontineChain. Mo wà níbí láti ràn ọ́ lọ́wọ́ pẹ̀lú owó rẹ. 🤖",
                'MONEY' => "Gbogbo owó tí o ti san jẹ́ " . number_format($data['total_paid'] ?? 0, 0, ',', ' ') . " FCFA. O ṣe gidi gan-an ! 💰",
                'TRUST' => "Ìwọ̀n ìgbàgbọ́ rẹ jẹ́ {$data['score']}/100. Tẹ̀síwájú láti máa san owó rẹ lákòókò. ⭐",
                'GROUPS' => isset($data['active_group']) ? "Ìgbà míràn tí o máa san owó fún ẹgbẹ́ '{$data['active_group']->name}' ni " . Carbon::parse($data['active_group']->next_due_date)->format('d/m/Y') . ". Má ṣe gbàgbé ! 📅" : "O kò sí nínú ẹgbẹ́ kankan lọ́wọ́lọ́wọ́.",
                'RANKING' => "O wà ní ipò kẹfà {$data['rank']} nínú àwọn ènìyàn {$data['total_users']}. O ṣeun ! 🏆",
                'BLOCKCHAIN' => "Owó rẹ wà ní àyè ìfọ̀kànbalẹ̀ lórí blockchain. 🔒",
                'INSURANCE' => "Ètò àyẹ̀wò owó wà fún ọ. 🛡️",
                'MEMBERS' => "Àwọn ọmọ ẹgbẹ́ rẹ wà níbí. 👥",
                'HELP' => "O lè bi mí ní ìbéèrè nípa owó rẹ, ìwọ̀n ìgbàgbọ́ rẹ, tàbí ẹgbẹ́ rẹ. 💡",
                'GENERAL' => "Mi ò mọ̀ ohun tí o sọ dáadáa, ṣùgbọ́n mo wà níbí láti ràn ọ́ lọ́wọ́. 👋"
            ]
        ];

        $lang = isset($responses[$this->locale]) ? $this->locale : 'fr';
        $text = $responses[$lang][$intent] ?? $responses[$lang]['GENERAL'];

        return $text;
    }
}
