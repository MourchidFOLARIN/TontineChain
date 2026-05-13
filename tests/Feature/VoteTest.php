<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_member_cannot_propose_position_swap()
    {
        Mail::fake();

        $creator = User::create(['phone' => '+22997000001', 'full_name' => 'Creator']);
        $member = User::create(['phone' => '+22997000002', 'full_name' => 'Member']);
        $outsider = User::create(['phone' => '+22997000003', 'full_name' => 'Outsider']);

        $group = Group::create([
            'name' => 'Vote Tontine',
            'creator_id' => $creator->id,
            'contribution_amount' => 1000,
            'max_members' => 2,
            'current_members' => 2,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'active',
            'current_cycle' => 1,
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $creator->id, 'position' => 1, 'status' => 'active']);
        GroupMember::create(['group_id' => $group->id, 'user_id' => $member->id, 'position' => 2, 'status' => 'active']);

        Sanctum::actingAs($outsider);

        $this->postJson("/api/v1/groups/{$group->id}/propose-swap", [
            'target_user_id' => $member->id,
        ])->assertStatus(403);
    }

    public function test_approved_swap_exchanges_positions_without_constraint_conflict()
    {
        Mail::fake();

        $first = User::create(['phone' => '+22997000001', 'full_name' => 'First']);
        $second = User::create(['phone' => '+22997000002', 'full_name' => 'Second']);

        $group = Group::create([
            'name' => 'Swap Tontine',
            'creator_id' => $first->id,
            'contribution_amount' => 1000,
            'max_members' => 2,
            'current_members' => 2,
            'frequency' => 'weekly',
            'payout_method' => 'sequential',
            'status' => 'active',
            'current_cycle' => 1,
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $first->id, 'position' => 1, 'status' => 'active']);
        GroupMember::create(['group_id' => $group->id, 'user_id' => $second->id, 'position' => 2, 'status' => 'active']);

        Sanctum::actingAs($first);

        $voteId = $this->postJson("/api/v1/groups/{$group->id}/propose-swap", [
            'target_user_id' => $second->id,
        ])->assertCreated()->json('id');

        $this->postJson("/api/v1/votes/{$voteId}/cast", ['choice' => true])
            ->assertOk();

        Sanctum::actingAs($second);

        $this->postJson("/api/v1/votes/{$voteId}/cast", ['choice' => true])
            ->assertOk();

        $this->assertDatabaseHas('group_members', [
            'group_id' => $group->id,
            'user_id' => $first->id,
            'position' => 2,
        ]);
        $this->assertDatabaseHas('group_members', [
            'group_id' => $group->id,
            'user_id' => $second->id,
            'position' => 1,
        ]);
        $this->assertSame('approved', Vote::find($voteId)->status);
    }
}
