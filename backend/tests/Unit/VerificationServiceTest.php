<?php

namespace Tests\Unit;

use App\Services\VerificationService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VerificationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_verified_code_issues_a_single_use_registration_token(): void
    {
        $email = 'USER@example.com';

        Cache::put('verify:email:user@example.com', json_encode([
            'email' => 'user@example.com',
            'name' => '사용자',
            'code' => '123456',
        ]), now()->addMinutes(30));

        $service = app(VerificationService::class);
        $token = $service->verifyCode($email, '123456');

        $this->assertNotNull($token);
        $this->assertSame(64, strlen($token));
        $this->assertTrue($service->isRegistrationTokenValid($email, $token));
        $this->assertNull(Cache::get('verify:email:user@example.com'));

        $service->consumeRegistrationToken($email, $token);

        $this->assertFalse($service->isRegistrationTokenValid($email, $token));
    }

    public function test_invalid_code_does_not_issue_a_registration_token(): void
    {
        Cache::put('verify:email:user@example.com', json_encode([
            'email' => 'user@example.com',
            'name' => '사용자',
            'code' => '123456',
        ]), now()->addMinutes(30));

        $service = app(VerificationService::class);

        $this->assertNull($service->verifyCode('user@example.com', '000000'));
    }
}
