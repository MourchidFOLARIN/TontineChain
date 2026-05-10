<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_group()
    {
        $user = User::create([
            'phone' => '+22997000000',
            'full_name' => 'Test User',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/groups', [
            'name' => 'Ma Tontine',
            'contribution_amount' => 5000,
            'max_members' => 5,
            'frequency' => 'monthly',
            'payout_method' => 'sequential',
            'start_date' => now()->addDays(7)->toIso8601String(),
        ]);

        $response->assertStatus(201)
                 ->assertJson(['name' => 'Ma Tontine']);

        $this->assertDatabaseHas('groups', ['name' => 'Ma Tontine', 'creator_id' => $user->id]);
        $this->assertDatabaseHas('group_members', ['user_id' => $user->id, 'position' => 1]);
    }

    public function test_user_can_join_invited_group()
    {
        $creator = User::create(['phone' => '+22997000001', 'full_name' => 'Creator']);
        $invitee = User::create(['phone' => '+22997000002', 'full_name' => 'Invitee']);

        $group = Group::create([
            'name' => 'Tontine Test',
            'creator_id' => $creator->id,
            'contribution_amount' => 1000,
            'max_members' => 2,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
        ]);

        // Mock invitation
        \App\Models\GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $invitee->id,
            'position' => 2,
            'status' => 'invited',
        ]);

        Sanctum::actingAs($invitee);

        $response = $this->postJson("/api/v1/groups/{$group->id}/join");

        $response->assertStatus(200);
        $this->assertDatabaseHas('group_members', [
            'group_id' => $group->id,
            'user_id' => $invitee->id,
            'status' => 'active'
        ]);
    }
}
