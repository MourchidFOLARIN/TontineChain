<?php

namespace Tests\Feature;

use App\Models\Bid;
use App\Models\Contribution;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Incident;
use App\Models\Payout;
use App\Models\TontineNotification;
use App\Models\User;
use App\Models\Vote;
use App\Services\RiskAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Tests\TestCase;

class ApiEndpointCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_health_login_docs_and_leaderboard_endpoints()
    {
        $userA = User::create(['full_name' => 'Alice', 'score_confiance' => 70, 'is_active' => true]);
        $userB = User::create(['full_name' => 'Bob', 'score_confiance' => 80, 'is_active' => true]);

        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->getJson('/api/v1/login')
            ->assertStatus(401)
            ->assertJson(['error' => 'Unauthenticated']);

        $this->get('/api/v1/docs')
            ->assertRedirect('/api/documentation');

        $this->getJson('/api/v1/users/leaderboard')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => $userA->id])
            ->assertJsonFragment(['id' => $userB->id]);
    }

    public function test_authenticated_user_profile_flow_and_balance()
    {
        $user = User::create(['phone' => '+22997000001', 'full_name' => 'Test User', 'score_confiance' => 55]);
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/users/me', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test.user@example.com',
            'profession' => 'Commerçant',
            'npi' => '1234567890',
            'preferred_language' => 'fr',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Profil mis à jour avec succès']);

        $this->postJson('/api/v1/user/profile', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '+22997000001',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Profil complété avec succès']);

        $this->getJson('/api/v1/users/me')
            ->assertOk()
            ->assertJsonFragment(['full_name' => 'Test User']);

        $this->getJson('/api/v1/user')
            ->assertOk()
            ->assertJsonFragment(['full_name' => 'Test User']);

        $balanceGroup1 = Group::create([
            'name' => 'Balance Group 1',
            'creator_id' => $user->id,
            'contribution_amount' => 1000,
            'max_members' => 1,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'active',
        ]);

        Contribution::create([
            'group_id' => $balanceGroup1->id,
            'user_id' => $user->id,
            'cycle_number' => 1,
            'amount_fcfa' => 1000,
            'status' => 'confirmed',
            'due_date' => now()->addDay(),
        ]);

        $balanceGroup2 = Group::create([
            'name' => 'Balance Group 2',
            'creator_id' => $user->id,
            'contribution_amount' => 1000,
            'max_members' => 1,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'active',
        ]);

        Payout::create([
            'group_id' => $balanceGroup2->id,
            'beneficiary_id' => $user->id,
            'cycle_number' => 1,
            'amount_fcfa' => 2000,
            'total_amount_fcfa' => 2000,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->getJson('/api/v1/users/me/score')
            ->assertOk()
            ->assertJsonStructure(['score_confiance', 'incidents']);

        $this->getJson('/api/v1/users/me/payouts')
            ->assertOk()
            ->assertJsonCount(1);

        $this->getJson('/api/v1/users/me/balance')
            ->assertOk()
            ->assertJsonStructure(['total_cotise_fcfa', 'total_recu_fcfa', 'expected_payouts_fcfa', 'currency', 'trust_score']);
    }

    public function test_group_routes_invite_messages_and_stats_work_as_expected()
    {
        Mail::fake();

        $creator = User::create(['phone' => '+22997000011', 'full_name' => 'Creator']);
        $invitee = User::create(['phone' => '+22997000022', 'full_name' => 'Invitee']);

        $group = Group::create([
            'name' => 'Coverage Group',
            'creator_id' => $creator->id,
            'contribution_amount' => 5000,
            'max_members' => 3,
            'current_members' => 1,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'pending',
            'total_cycles' => 3,
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $creator->id, 'position' => 1, 'status' => 'active']);

        Sanctum::actingAs($creator);

        $this->getJson('/api/v1/groups')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Coverage Group']);

        $this->getJson("/api/v1/groups/{$group->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $group->id]);

        $this->postJson("/api/v1/groups/{$group->id}/invite", [
            'phone' => '+22997000033',
            'email' => 'newinvite@example.com',
        ])
            ->assertOk()
            ->assertJsonStructure(['message', 'member_analysis', 'demo_notice']);

        $group->refresh();
        $this->assertSame(2, $group->current_members);

        $this->mock(RiskAnalysisService::class, function (MockInterface $mock) {
            $mock->shouldReceive('analyzeGroupRisk')->once()->andReturn(['score' => 42, 'recommendation' => 'Respecter les échéances']);
        });

        Contribution::create([
            'group_id' => $group->id,
            'user_id' => $creator->id,
            'cycle_number' => 1,
            'amount_fcfa' => 5000,
            'status' => 'confirmed',
            'due_date' => now()->subDay(),
        ]);

        Payout::create([
            'group_id' => $group->id,
            'beneficiary_id' => $creator->id,
            'cycle_number' => 1,
            'amount_fcfa' => 10000,
            'total_amount_fcfa' => 10000,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->getJson("/api/v1/groups/{$group->id}/stats")
            ->assertOk()
            ->assertJsonStructure(['group_id', 'name', 'payout_method', 'insurance', 'stats', 'ai_risk_prediction']);

        $this->getJson("/api/v1/groups/{$group->id}/contract")
            ->assertStatus(404)
            ->assertJson(['error' => "La tontine n'a pas encore démarré."]);

        $member = User::where('phone', '+22997000033')->first();
        $this->assertNotNull($member);

        Sanctum::actingAs($member);

        $this->postJson("/api/v1/groups/{$group->id}/join")
            ->assertOk()
            ->assertJson(['message' => 'Bienvenue dans le groupe !']);

        $group->refresh();
        $group->update(['status' => 'active']);

        $this->postJson("/api/v1/groups/{$group->id}/messages", ['content' => 'Bonjour le groupe'])
            ->assertCreated()
            ->assertJsonFragment(['content' => 'Bonjour le groupe']);

        $this->getJson("/api/v1/groups/{$group->id}/messages")
            ->assertOk()
            ->assertJsonFragment(['content' => 'Bonjour le groupe']);
    }

    public function test_payouts_incidents_notifications_and_votes_endpoints()
    {
        Mail::fake();

        $user = User::create(['phone' => '+22997000044', 'full_name' => 'Payout User', 'score_confiance' => 90]);
        Sanctum::actingAs($user);

        $group = Group::create([
            'name' => 'Service Group',
            'creator_id' => $user->id,
            'contribution_amount' => 1200,
            'max_members' => 2,
            'frequency' => 'weekly',
            'payout_method' => 'bidding',
            'status' => 'active',
            'current_cycle' => 1,
            'total_cycles' => 2,
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $user->id, 'position' => 1, 'status' => 'active']);

        $payout = Payout::create([
            'group_id' => $group->id,
            'beneficiary_id' => $user->id,
            'cycle_number' => 1,
            'amount_fcfa' => 2000,
            'total_amount_fcfa' => 2000,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $incident = Incident::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'type' => 'late_payment',
            'description' => 'Retard test',
            'cycle_number' => 1,
            'score_impact' => -10,
            'occurred_at' => now(),
        ]);

        $notification = TontineNotification::create([
            'user_id' => $user->id,
            'type' => 'payment_due',
            'message' => 'Paiement à effectuer',
            'channel' => 'in_app',
        ]);

        $vote = Vote::create([
            'group_id' => $group->id,
            'creator_id' => $user->id,
            'type' => 'swap_positions',
            'proposal_data' => ['user_a' => $user->id, 'user_b' => $user->id],
            'required_votes' => 1,
            'expires_at' => now()->addDay(),
        ]);

        $this->getJson('/api/v1/payouts')
            ->assertOk()
            ->assertJsonFragment(['id' => $payout->id]);

        $this->getJson("/api/v1/payouts/{$payout->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $payout->id]);

        $this->getJson('/api/v1/incidents')
            ->assertOk()
            ->assertJsonPath('incidents.0.id', $incident->id);

        $this->getJson("/api/v1/incidents/{$incident->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $incident->id]);

        $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('notifications.0.id', $notification->id);

        $this->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonFragment(['is_read' => true]);

        $this->getJson('/api/v1/votes')
            ->assertOk()
            ->assertJsonFragment(['id' => $vote->id]);

        $this->getJson("/api/v1/votes/{$vote->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $vote->id]);

        $this->postJson("/api/v1/votes/{$vote->id}/cast", ['choice' => true])
            ->assertOk();
    }

    public function test_bidding_and_ai_chat_endpoints()
    {
        $creator = User::create(['phone' => '+22997000055', 'full_name' => 'Bid User']);
        $member = User::create(['phone' => '+22997000056', 'full_name' => 'Bid Member']);

        $group = Group::create([
            'name' => 'Bid Group',
            'creator_id' => $creator->id,
            'contribution_amount' => 2000,
            'max_members' => 2,
            'frequency' => 'weekly',
            'payout_method' => 'bidding',
            'status' => 'active',
            'current_cycle' => 1,
            'total_cycles' => 2,
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $creator->id, 'position' => 1, 'status' => 'active']);
        GroupMember::create(['group_id' => $group->id, 'user_id' => $member->id, 'position' => 2, 'status' => 'active']);

        Sanctum::actingAs($member);

        $this->postJson("/api/v1/groups/{$group->id}/bid", ['discount_amount' => 100])
            ->assertCreated()
            ->assertJsonStructure(['message', 'bid']);

        $this->getJson("/api/v1/groups/{$group->id}/bids")
            ->assertOk()
            ->assertJsonCount(1);

        $this->postJson('/api/v1/ai/chat', ['message' => 'quel est mon score ?', 'locale' => 'fr'])
            ->assertOk()
            ->assertJsonStructure(['assistant', 'message', 'audio_url', 'demo_notice']);
    }
}
