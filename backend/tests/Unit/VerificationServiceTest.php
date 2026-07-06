<?php

namespace Tests\Unit;

use App\Exceptions\Mail\VerificationMailException;
use App\Services\Mail\VerificationService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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

    public function test_verification_code_is_cached_after_mail_request_is_accepted(): void
    {
        $this->configureMailerSend();
        Http::fake([
            'https://api.mailersend.test/*' => Http::response(status: 202),
        ]);

        $service = app(VerificationService::class);
        $service->generateCodeAndSend('USER@example.com', '사용자');

        $codeData = $service->getCodeData('user@example.com');

        $this->assertNotNull($codeData);
        $this->assertSame('user@example.com', $codeData['email']);
        $this->assertSame('사용자', $codeData['name']);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $codeData['code']);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.mailersend.test/v1/email'
                && $request['to'][0]['email'] === 'user@example.com'
                && str_contains($request['text'], '5분 이내');
        });
    }

    public function test_verification_code_is_removed_when_mail_request_fails(): void
    {
        $this->configureMailerSend();
        Http::fake([
            'https://api.mailersend.test/*' => Http::response(status: 500),
        ]);

        $service = app(VerificationService::class);

        try {
            $service->generateCodeAndSend('user@example.com', '사용자');
            $this->fail('VerificationMailException was not thrown.');
        } catch (VerificationMailException) {
            $this->assertNull($service->getCodeData('user@example.com'));
        }
    }

    private function configureMailerSend(): void
    {
        config()->set('services.mailersend', [
            'key' => 'test-api-key',
            'endpoint' => 'https://api.mailersend.test/v1/email',
            'from' => [
                'address' => 'sender@example.com',
                'name' => 'AI 서비스',
            ],
            'connect_timeout' => 1,
            'timeout' => 1,
        ]);
    }
}
