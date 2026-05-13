<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Group;
use App\Models\GroupMember;
use App\Services\BlockchainService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;

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

    public function test_non_member_cannot_view_group_details()
    {
        $creator = User::create(['phone' => '+22997000001', 'full_name' => 'Creator']);
        $outsider = User::create(['phone' => '+22997000002', 'full_name' => 'Outsider']);

        $group = Group::create([
            'name' => 'Private Tontine',
            'creator_id' => $creator->id,
            'contribution_amount' => 1000,
            'max_members' => 2,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
        ]);

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $creator->id,
            'position' => 1,
            'status' => 'active',
        ]);

        Sanctum::actingAs($outsider);

        $this->getJson("/api/v1/groups/{$group->id}")
            ->assertStatus(403);
    }

    public function test_creator_cannot_start_group_until_all_invited_members_join()
    {
        $creator = User::create(['phone' => '+22997000001', 'full_name' => 'Creator']);
        $invitee = User::create(['phone' => '+22997000002', 'full_name' => 'Invitee']);

        $group = Group::create([
            'name' => 'Pending Tontine',
            'creator_id' => $creator->id,
            'contribution_amount' => 1000,
            'max_members' => 2,
            'current_members' => 2,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'pending',
            'start_date' => now()->addDays(7),
        ]);

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $creator->id,
            'position' => 1,
            'status' => 'active',
        ]);

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $invitee->id,
            'position' => 2,
            'status' => 'invited',
        ]);

        Sanctum::actingAs($creator);

        $this->postJson("/api/v1/groups/{$group->id}/start")
            ->assertStatus(400)
            ->assertJson(['error' => 'Groupe incomplet']);
    }

    public function test_starting_group_creates_first_cycle_contributions()
    {
        Mail::fake();

        $creator = User::create([
            'phone' => '+22997000001',
            'email' => 'creator@example.com',
            'full_name' => 'Creator',
            'wallet_address' => '0x1111111111111111111111111111111111111111',
        ]);
        $member = User::create([
            'phone' => '+22997000002',
            'email' => 'member@example.com',
            'full_name' => 'Member',
            'wallet_address' => '0x2222222222222222222222222222222222222222',
        ]);

        $group = Group::create([
            'name' => 'Ready Tontine',
            'creator_id' => $creator->id,
            'contribution_amount' => 2500,
            'max_members' => 2,
            'current_members' => 2,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'pending',
            'start_date' => now()->addDays(7),
            'total_cycles' => 2,
        ]);

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $creator->id,
            'position' => 1,
            'status' => 'active',
        ]);
        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $member->id,
            'position' => 2,
            'status' => 'active',
        ]);

        $this->mock(BlockchainService::class, function (MockInterface $mock) {
            $mock->shouldReceive('deployTontineContract')->once()->andReturn([
                'contract_address' => '0x3333333333333333333333333333333333333333',
                'tx_hash' => '0xbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
                'block_number' => '0x1',
            ]);
        });

        Sanctum::actingAs($creator);

        $this->postJson("/api/v1/groups/{$group->id}/start")
            ->assertOk();

        $this->assertDatabaseHas('contributions', [
            'group_id' => $group->id,
            'user_id' => $creator->id,
            'cycle_number' => 1,
            'amount_fcfa' => 2500,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('contributions', [
            'group_id' => $group->id,
            'user_id' => $member->id,
            'cycle_number' => 1,
            'amount_fcfa' => 2500,
            'status' => 'pending',
        ]);
    }

    public function test_non_member_cannot_read_group_messages()
    {
        $creator = User::create(['phone' => '+22997000001', 'full_name' => 'Creator']);
        $outsider = User::create(['phone' => '+22997000002', 'full_name' => 'Outsider']);

        $group = Group::create([
            'name' => 'Chat Tontine',
            'creator_id' => $creator->id,
            'contribution_amount' => 1000,
            'max_members' => 2,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'active',
        ]);

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $creator->id,
            'position' => 1,
            'status' => 'active',
        ]);

        Sanctum::actingAs($outsider);

        $this->getJson("/api/v1/groups/{$group->id}/messages")
            ->assertStatus(403);
    }
}
