<?php

namespace Tests\Feature;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock all external HTTP calls (Infobip SMS/WhatsApp/Email)
        Http::fake([
            '*' => Http::response(['messages' => [['status' => ['name' => 'PENDING_ACCEPTED']]]], 200),
        ]);
    }

    public function test_user_can_request_otp()
    {
        $response = $this->postJson('/api/v1/auth/request-otp', [
            'phone' => '+22997000000',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'message', 'phone']);

        $this->assertDatabaseHas('otps', [
            'is_used' => false,
        ]);
    }

    public function test_user_can_verify_otp_and_login()
    {
        // 1. Setup OTP
        $phone = '+22997000000';
        $code = '123456';
        $codeHash = hash('sha256', $code);

        Otp::create([
            'phone' => $phone,
            'code_hash' => $codeHash,
            'expires_at' => now()->addMinutes(5),
            'purpose' => 'login'
        ]);

        // 2. Verify
        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'phone' => $phone,
            'code' => $code,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token', 'user']);

        $this->assertDatabaseHas('users', ['phone' => $phone]);
        
        $otp = Otp::where('phone', $phone)->first();
        $this->assertTrue($otp->is_used);
    }

    public function test_invalid_otp_fails()
    {
        $phone = '+22997000000';
        
        Otp::create([
            'phone' => $phone,
            'code_hash' => hash('sha256', '111111'),
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'phone' => $phone,
            'code' => '222222', // Wrong code
        ]);

        $response->assertStatus(401);
    }
}
