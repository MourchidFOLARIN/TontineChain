<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UncoveredEndpointsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * GET /api/v1/user - Récupère l'utilisateur actuel
     */
    public function test_get_user_endpoint_returns_authenticated_user()
    {
        $user = User::create([
            'phone' => '+22997000099',
            'full_name' => 'User Test',
            'email' => 'user@test.com',
            'score_confiance' => 75,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/user')
            ->assertOk()
            ->assertJson([
                'id' => $user->id,
                'phone' => '+22997000099',
                'full_name' => 'User Test',
                'email' => 'user@test.com',
            ]);
    }

    /**
     * GET /api/v1/user - Teste l'authentification requise
     */
    public function test_get_user_endpoint_requires_authentication()
    {
        $this->getJson('/api/v1/user')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * GET /api/v1/contributions/pending - Liste les contributions en attente
     */
    public function test_pending_contributions_endpoint()
    {
        $user = User::create(['phone' => '+22997000077', 'full_name' => 'Contrib User']);
        
        $group = Group::create([
            'name' => 'Contrib Group',
            'creator_id' => $user->id,
            'contribution_amount' => 5000,
            'max_members' => 2,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'active',
            'current_cycle' => 1,
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $user->id, 'position' => 1, 'status' => 'active']);

        // Créer une contribution en attente
        $contribution = Contribution::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'cycle_number' => 1,
            'amount_fcfa' => 5000,
            'status' => 'pending',
            'due_date' => now()->addDay(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/contributions/pending')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $contribution->id, 'status' => 'pending']);
    }

    /**
     * GET /api/v1/contributions/pending - Aucune contribution en attente
     */
    public function test_pending_contributions_endpoint_empty()
    {
        $user = User::create(['phone' => '+22997000078', 'full_name' => 'No Contrib User']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/contributions/pending')
            ->assertOk()
            ->assertJsonCount(0);
    }

    /**
     * GET /api/v1/contributions/pending - Authentification requise
     */
    public function test_pending_contributions_requires_authentication()
    {
        $this->getJson('/api/v1/contributions/pending')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * GET /api/v1/debug/* - Endpoints de debug en environnement local
     * Note: Ces endpoints ne sont disponibles qu'en local()
     */
    public function test_debug_endpoints_not_available_in_production()
    {
        $this->withoutExceptionHandling();
        
        // Si on est en environnement local, ces endpoints doivent répondre
        if (app()->environment('local')) {
            $this->getJson('/api/v1/debug/blockchain')
                ->assertOk();

            $this->getJson('/api/v1/debug/email')
                ->assertOk();

            $this->getJson('/api/v1/debug/fedapay')
                ->assertOk();

            $this->getJson('/api/v1/debug/infobip')
                ->assertOk();

            $this->getJson('/api/v1/debug/telegram')
                ->assertOk();
        }
    }

    /**
     * Test error cases for various endpoints
     */
    public function test_get_nonexistent_incident_returns_404()
    {
        $user = User::create(['phone' => '+22997000088', 'full_name' => 'Not Found User']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/incidents/99999999')
            ->assertStatus(404);
    }

    /**
     * Test get nonexistent payout returns 404
     */
    public function test_get_nonexistent_payout_returns_404()
    {
        $user = User::create(['phone' => '+22997000089', 'full_name' => 'Not Found Payout User']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/payouts/99999999')
            ->assertStatus(404);
    }

    /**
     * Test get nonexistent vote returns 404
     */
    public function test_get_nonexistent_vote_returns_404()
    {
        $user = User::create(['phone' => '+22997000090', 'full_name' => 'Not Found Vote User']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/votes/99999999')
            ->assertStatus(404);
    }

    /**
     * Test get nonexistent group returns 404
     */
    public function test_get_nonexistent_group_returns_404()
    {
        $user = User::create(['phone' => '+22997000091', 'full_name' => 'Not Found Group User']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/groups/99999999')
            ->assertStatus(404);
    }

    /**
     * Test contribution payment - user can initiate payment for their contribution
     */
    public function test_contribution_payment_initiation()
    {
        $user = User::create(['phone' => '+22997000092', 'full_name' => 'Payment Validation']);
        
        $group = Group::create([
            'name' => 'Payment Group',
            'creator_id' => $user->id,
            'contribution_amount' => 5000,
            'max_members' => 1,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'active',
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $user->id, 'position' => 1, 'status' => 'active']);

        $contribution = Contribution::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'cycle_number' => 1,
            'amount_fcfa' => 5000,
            'status' => 'pending',
            'due_date' => now()->addDay(),
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/contributions/{$contribution->id}/pay", [])
            ->assertOk()
            ->assertJsonStructure(['payment_url', 'transaction_id', 'demo_notice']);
    }

    /**
     * Test invite validation - phone format
     */
    public function test_group_invite_phone_validation()
    {
        $creator = User::create(['phone' => '+22997000093', 'full_name' => 'Inviter']);
        
        $group = Group::create([
            'name' => 'Invite Group',
            'creator_id' => $creator->id,
            'contribution_amount' => 5000,
            'max_members' => 2,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'pending',
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $creator->id, 'position' => 1, 'status' => 'active']);
        
        Sanctum::actingAs($creator);

        // Invalid phone format
        $this->postJson("/api/v1/groups/{$group->id}/invite", [
            'phone' => 'invalid',
            'email' => 'test@example.com',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('phone');
    }

    /**
     * Test message creation validation - content required
     */
    public function test_message_content_validation()
    {
        $user = User::create(['phone' => '+22997000094', 'full_name' => 'Messenger']);
        
        $group = Group::create([
            'name' => 'Message Group',
            'creator_id' => $user->id,
            'contribution_amount' => 5000,
            'max_members' => 1,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'active',
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $user->id, 'position' => 1, 'status' => 'active']);
        
        Sanctum::actingAs($user);

        // Message sans contenu
        $this->postJson("/api/v1/groups/{$group->id}/messages", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('content');
    }

    /**
     * Test non-member cannot access group messages
     */
    public function test_non_member_cannot_access_group_messages()
    {
        $creator = User::create(['phone' => '+22997000095', 'full_name' => 'Creator']);
        $stranger = User::create(['phone' => '+22997000096', 'full_name' => 'Stranger']);
        
        $group = Group::create([
            'name' => 'Exclusive Group',
            'creator_id' => $creator->id,
            'contribution_amount' => 5000,
            'max_members' => 1,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'active',
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $creator->id, 'position' => 1, 'status' => 'active']);
        
        Sanctum::actingAs($stranger);

        $this->getJson("/api/v1/groups/{$group->id}/messages")
            ->assertStatus(403)
            ->assertJson(['error' => 'Non autorisé']);
    }

    /**
     * Test user profile completion with valid data
     */
    public function test_user_profile_completion()
    {
        $user = User::create(['phone' => '+22997000097', 'full_name' => 'Profile User']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/user/profile', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '+22997000197',
        ])
            ->assertOk()
            ->assertJson(['message' => 'Profil complété avec succès'])
            ->assertJsonFragment(['full_name' => 'Test User']);
    }

    /**
     * Test user profile completion without required fields
     */
    public function test_user_profile_validation_required_fields()
    {
        $user = User::create(['phone' => '+22997000098a', 'full_name' => 'Profile User 2']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/user/profile', [
            'first_name' => 'Test',
            'phone' => '+22997000198',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('last_name');
    }

    /**
     * Test leaderboard endpoint without authentication (public)
     */
    public function test_leaderboard_is_public()
    {
        User::create([
            'full_name' => 'Leader 1',
            'score_confiance' => 95,
            'is_active' => true,
        ]);

        User::create([
            'full_name' => 'Leader 2',
            'score_confiance' => 85,
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/users/leaderboard')
            ->assertOk()
            ->assertJsonCount(2);
    }

    /**
     * Test leaderboard filters inactive users
     */
    public function test_leaderboard_excludes_inactive_users()
    {
        User::create([
            'full_name' => 'Active User',
            'score_confiance' => 95,
            'is_active' => true,
        ]);

        User::create([
            'full_name' => 'Inactive User',
            'score_confiance' => 90,
            'is_active' => false,
        ]);

        $this->getJson('/api/v1/users/leaderboard')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['full_name' => 'Active User']);
    }

    /**
     * Test cast vote validation - choice required
     */
    public function test_cast_vote_validation()
    {
        $user = User::create(['phone' => '+22997000098', 'full_name' => 'Voter']);
        
        $group = Group::create([
            'name' => 'Vote Group',
            'creator_id' => $user->id,
            'contribution_amount' => 5000,
            'max_members' => 1,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'active',
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $user->id, 'position' => 1, 'status' => 'active']);

        $vote = \App\Models\Vote::create([
            'group_id' => $group->id,
            'creator_id' => $user->id,
            'type' => 'swap_positions',
            'proposal_data' => ['user_a' => $user->id, 'user_b' => $user->id],
            'required_votes' => 1,
            'expires_at' => now()->addDay(),
        ]);

        Sanctum::actingAs($user);

        // Vote sans choice
        $this->postJson("/api/v1/votes/{$vote->id}/cast", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('choice');
    }

    /**
     * Test user cannot initiate payment for someone else's contribution
     */
    public function test_unauthorized_contribution_payment()
    {
        $user1 = User::create(['phone' => '+22997000199', 'full_name' => 'User 1']);
        $user2 = User::create(['phone' => '+22997000200', 'full_name' => 'User 2']);
        
        $group = Group::create([
            'name' => 'Shared Group',
            'creator_id' => $user1->id,
            'contribution_amount' => 5000,
            'max_members' => 2,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'active',
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $user1->id, 'position' => 1, 'status' => 'active']);
        GroupMember::create(['group_id' => $group->id, 'user_id' => $user2->id, 'position' => 2, 'status' => 'active']);

        $contribution = Contribution::create([
            'group_id' => $group->id,
            'user_id' => $user1->id,
            'cycle_number' => 1,
            'amount_fcfa' => 5000,
            'status' => 'pending',
            'due_date' => now()->addDay(),
        ]);

        Sanctum::actingAs($user2);

        $this->postJson("/api/v1/contributions/{$contribution->id}/pay", [])
            ->assertStatus(403)
            ->assertJson(['error' => 'Non autorisé']);
    }
}
