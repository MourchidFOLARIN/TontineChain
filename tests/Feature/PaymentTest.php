<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Group;
use App\Models\Contribution;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
