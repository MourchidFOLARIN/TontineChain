<?php

namespace Tests\Feature;

use App\Models\Bid;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Contribution;
use App\Models\Payout;
use App\Services\BlockchainService;
use App\Services\PaymentService;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_initiate_payment()
    {
        $user = User::create(['phone' => '+22997000000', 'full_name' => 'User']);
        $group = Group::create([
            'name' => 'Tontine',
            'creator_id' => $user->id,
            'contribution_amount' => 1000,
            'max_members' => 2,
            'frequency' => 'weekly',
        ]);

        $contribution = Contribution::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'cycle_number' => 1,
            'amount_fcfa' => 1000,
            'status' => 'pending',
            'due_date' => now()->addDays(1),
        ]);

        // Mock PaymentService
        $this->mock(PaymentService::class, function (MockInterface $mock) use ($contribution) {
            $mock->shouldReceive('initiatePayment')
                 ->once()
                 ->andReturn(['payment_url' => 'https://mock-fedapay.com/pay', 'transaction_id' => '12345']);
        });

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/contributions/{$contribution->id}/pay");

        $response->assertStatus(200)
                 ->assertJson(['payment_url' => 'https://mock-fedapay.com/pay']);
    }

    public function test_user_can_initiate_late_contribution_payment()
    {
        $user = User::create(['phone' => '+22997000000', 'full_name' => 'User']);
        $group = Group::create([
            'name' => 'Tontine',
            'creator_id' => $user->id,
            'contribution_amount' => 1000,
            'max_members' => 2,
            'frequency' => 'weekly',
        ]);

        $contribution = Contribution::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'cycle_number' => 1,
            'amount_fcfa' => 1000,
            'status' => 'late',
            'due_date' => now()->subDay(),
            'is_late' => true,
            'late_days' => 1,
        ]);

        $this->mock(PaymentService::class, function (MockInterface $mock) {
            $mock->shouldReceive('initiatePayment')
                ->once()
                ->andReturn(['payment_url' => 'https://mock-fedapay.com/pay', 'transaction_id' => '12345']);
        });

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/contributions/{$contribution->id}/pay")
            ->assertStatus(200)
            ->assertJson(['payment_url' => 'https://mock-fedapay.com/pay']);

        $this->getJson('/api/v1/contributions/pending')
            ->assertOk()
            ->assertJsonFragment(['id' => $contribution->id]);
    }

    public function test_fedapay_webhook_uses_winning_bid_for_payout()
    {
        Mail::fake();

        $creator = User::create([
            'phone' => '+22997000000',
            'email' => 'creator@example.com',
            'full_name' => 'Creator',
        ]);

        $winner = User::create([
            'phone' => '+22997000001',
            'email' => 'winner@example.com',
            'full_name' => 'Winner',
        ]);

        $group = Group::create([
            'name' => 'Tontine Bidding',
            'creator_id' => $creator->id,
            'contract_address' => '0x1111111111111111111111111111111111111111',
            'contribution_amount' => 1000,
            'max_members' => 2,
            'current_members' => 2,
            'frequency' => 'weekly',
            'payout_method' => 'bidding',
            'current_cycle' => 1,
            'total_cycles' => 2,
            'status' => 'active',
            'insurance_percent' => 0,
            'next_due_date' => now(),
        ]);

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $creator->id,
            'position' => 1,
            'status' => 'active',
        ]);

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $winner->id,
            'position' => 2,
            'status' => 'active',
        ]);

        Contribution::create([
            'group_id' => $group->id,
            'user_id' => $creator->id,
            'cycle_number' => 1,
            'amount_fcfa' => 1000,
            'status' => 'confirmed',
            'due_date' => now()->addDay(),
        ]);

        Contribution::create([
            'group_id' => $group->id,
            'user_id' => $winner->id,
            'cycle_number' => 1,
            'amount_fcfa' => 1000,
            'status' => 'pending',
            'fedapay_transaction_id' => 'fedapay-123',
            'due_date' => now()->addDay(),
        ]);

        Bid::create([
            'group_id' => $group->id,
            'user_id' => $creator->id,
            'cycle_number' => 1,
            'discount_amount' => 100,
        ]);

        Bid::create([
            'group_id' => $group->id,
            'user_id' => $winner->id,
            'cycle_number' => 1,
            'discount_amount' => 300,
        ]);

        $this->mock(BlockchainService::class, function (MockInterface $mock) {
            $mock->shouldReceive('releasePayout')
                ->once()
                ->andReturn('0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
        });

        $this->mock(TelegramService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendPaymentAlert')->once()->andReturn(true);
        });

        $response = $this
            ->withHeader('X-Fedapay-Signature', env('FEDAPAY_WEBHOOK_SECRET'))
            ->postJson('/api/v1/webhooks/fedapay', [
                'event' => 'transaction.approved',
                'entity' => ['id' => 'fedapay-123'],
            ]);

        $response->assertOk()->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('payouts', [
            'group_id' => $group->id,
            'beneficiary_id' => $winner->id,
            'cycle_number' => 1,
            'amount_fcfa' => 1700,
            'blockchain_tx_hash' => '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
        ]);

        $this->assertDatabaseHas('bids', [
            'group_id' => $group->id,
            'user_id' => $winner->id,
            'status' => 'won',
        ]);

        $this->assertDatabaseHas('group_members', [
            'group_id' => $group->id,
            'user_id' => $winner->id,
            'has_received' => true,
            'cycle_received' => 1,
        ]);

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'current_cycle' => 2,
            'insurance_fund' => 300,
        ]);

        $this->assertDatabaseHas('contributions', [
            'group_id' => $group->id,
            'user_id' => $creator->id,
            'cycle_number' => 2,
            'amount_fcfa' => 1000,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('contributions', [
            'group_id' => $group->id,
            'user_id' => $winner->id,
            'cycle_number' => 2,
            'amount_fcfa' => 1000,
            'status' => 'pending',
        ]);

        $this->assertSame(1, Payout::count());
    }

    public function test_fedapay_webhook_rejects_approved_transaction_without_id()
    {
        $response = $this
            ->withHeader('X-Fedapay-Signature', env('FEDAPAY_WEBHOOK_SECRET'))
            ->postJson('/api/v1/webhooks/fedapay', [
                'event' => 'transaction.approved',
                'entity' => [],
            ]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Invalid webhook payload']);
    }
}
