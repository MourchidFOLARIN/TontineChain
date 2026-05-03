<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TontineSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Création de l'utilisateur Démo (Mourchid)
        $mourchid = User::create([
            'id' => (string) Str::uuid(),
            'phone' => '+22900000000',
            'first_name' => 'Mourchid',
            'last_name' => 'F.',
            'full_name' => 'Mourchid F.',
            'profession' => 'Commerçant Import-Export',
            'score_confiance' => 95,
            'kyc_status' => 'verified',
            'preferred_language' => 'fr',
            'is_active' => true,
        ]);

        // 2. Création de quelques membres Elite
        $koffi = User::create([
            'phone' => '+22911111111',
            'first_name' => 'Koffi',
            'last_name' => 'Adjovi',
            'full_name' => 'Koffi Adjovi',
            'score_confiance' => 99,
            'kyc_status' => 'verified',
        ]);

        $amina = User::create([
            'phone' => '+22922222222',
            'first_name' => 'Amina',
            'last_name' => 'Soule',
            'full_name' => 'Amina Soule',
            'score_confiance' => 97,
            'kyc_status' => 'verified',
        ]);

        // 3. Création d'un groupe actif
        $group = Group::create([
            'name' => 'Tontine Diamant',
            'creator_id' => $mourchid->id,
            'contribution_amount' => 50000,
            'max_members' => 10,
            'current_members' => 3,
            'frequency' => 'monthly',
            'payout_method' => 'bidding',
            'status' => 'active',
            'current_cycle' => 4,
            'start_date' => now()->subMonths(4),
            'next_due_date' => now()->addDays(15),
            'insurance_fund' => 150000,
        ]);

        // 4. Inscriptions
        GroupMember::create(['group_id' => $group->id, 'user_id' => $mourchid->id, 'position' => 1, 'status' => 'active', 'has_received' => false]);
        GroupMember::create(['group_id' => $group->id, 'user_id' => $koffi->id, 'position' => 2, 'status' => 'active', 'has_received' => true]);
        GroupMember::create(['group_id' => $group->id, 'user_id' => $amina->id, 'position' => 3, 'status' => 'active', 'has_received' => false]);
    }
}
