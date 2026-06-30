<?php
namespace App\Services;

use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

class VerificationService
{
    public function generateCode(string $email, string $name): string
    {
        $email = $this->normalizeEmail($email);
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $data = [
            'email' => $email,
            'name' => $name,
            'code' => $code,
        ];

        Cache::put("verify:email:$email", json_encode($data), now()->addMinutes(30));

        return $code;
    }

    public function getCodeData(string $email): ?array
    {
        $email = $this->normalizeEmail($email);
        $cached = Cache::get("verify:email:$email");

        return $cached ? json_decode($cached, true) : null;
    }

    /*
    public function generateCodeAndSend(string $email, string $name): void
    {
        $code = $this->generateCode($email, $name);
        Mail::to($email)->queue(new VerificationCodeMail($name, $code));
    }
    */
    public function generateCodeAndSend(string $email, string $name): void
    {
        $code = $this->generateCode($email, $name);

        try {
            $client = new Client();

            $response = $client->post('https://api.mailersend.com/v1/email', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.mailersend.key'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'from' => [
                        'email' => 'test@test-51ndgwv138xlzqx8.mlsender.net',
                        'name' => 'AI서비스',
                    ],
                    'to' => [
                        ['email' => $email, 'name' => $name],
                    ],
                    'subject' => '이메일 인증번호 안내',
                    'text' => "안녕하세요 {$name}님,\n\n인증번호는 [{$code}] 입니다.\n\n5분 이내에 입력해주세요.",
                ],
            ]);

            if ($response->getStatusCode() !== 202) {
                throw new \Exception("Mailersend 전송 실패: {$response->getStatusCode()}");
            }
        } catch (\Throwable $e) {
            Log::error("메일 전송 실패: " . $e->getMessage());
            throw new \Exception('메일 전송 중 문제가 발생했습니다.');
        }
    }
    public function verifyCode(string $email, string $code): ?string
    {
        $email = $this->normalizeEmail($email);
        $data = $this->getCodeData($email);

        if (!is_array($data) || !isset($data['code'])) {
            return null;
        }

        if (!hash_equals((string) $data['code'], $code)) {
            return null;
        }

        $token = Str::random(64);
        $tokenHash = hash('sha256', $token);
        $expiresAt = now()->addMinutes(30);

        Cache::put($this->registrationTokenKey($token), $email, $expiresAt);
        Cache::put("verified:email:$email", $tokenHash, $expiresAt);
        Cache::forget("verify:email:$email");

        return $token;
    }

    public function isRegistrationTokenValid(string $email, string $token): bool
    {
        $email = $this->normalizeEmail($email);
        $tokenHash = hash('sha256', $token);
        $verifiedTokenHash = Cache::get("verified:email:$email");
        $verifiedEmail = Cache::get($this->registrationTokenKey($token));

        return is_string($verifiedTokenHash)
            && is_string($verifiedEmail)
            && hash_equals($verifiedTokenHash, $tokenHash)
            && hash_equals($verifiedEmail, $email);
    }

    public function consumeRegistrationToken(string $email, string $token): void
    {
        $email = $this->normalizeEmail($email);

        Cache::forget($this->registrationTokenKey($token));
        Cache::forget("verified:email:$email");
    }

    public function clearCode(string $email): void
    {
        $email = $this->normalizeEmail($email);
        Cache::forget("verify:email:$email");
    }

    private function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }

    private function registrationTokenKey(string $token): string
    {
        return 'verify:registration:' . hash('sha256', $token);
    }
}
