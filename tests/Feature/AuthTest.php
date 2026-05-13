<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\Otp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            '*' => Http::response(['messages' => [['status' => ['name' => 'PENDING_ACCEPTED']]]], 200),
        ]);
    }

    public function test_user_can_request_otp(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/auth/request-otp', [
            'email' => 'test@example.com',
            'locale' => 'fr',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message', 'email']);

        Mail::assertSent(OtpMail::class, function (OtpMail $mail) {
            return $mail->hasTo('test@example.com');
        });

        $this->assertDatabaseHas('otps', [
            'email' => 'test@example.com',
            'is_used' => false,
        ]);
    }

    public function test_user_can_verify_otp_and_login(): void
    {
        $email = 'user@example.com';
        $code = '123456';
        $codeHash = hash('sha256', $code);

        Otp::create([
            'email' => $email,
            'code_hash' => $codeHash,
            'expires_at' => now()->addMinutes(5),
            'purpose' => 'login',
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'email' => $email,
            'code' => $code,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'user']);

        $this->assertDatabaseHas('users', ['email' => $email]);

        $otp = Otp::where('email', $email)->first();
        $this->assertTrue($otp->is_used);
    }

    public function test_invalid_otp_fails(): void
    {
        $email = 'late@example.com';

        Otp::create([
            'email' => $email,
            'code_hash' => hash('sha256', '111111'),
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'email' => $email,
            'code' => '222222',
        ]);

        $response->assertStatus(401);
    }
}
