<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class VerificationService
{
    private const CODE_TTL_MINUTES = 5;

    private const REGISTRATION_TOKEN_TTL_MINUTES = 30;

    public function __construct(
        private readonly MailerSendClient $mailerSendClient,
    ) {
    }

    public function generateCodeAndSend(string $email, string $name): void
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $code = $this->generateCode($normalizedEmail, $name);

        try {
            $this->mailerSendClient->sendVerificationCode(
                email: $normalizedEmail,
                name: $name,
                code: $code,
                expiresInMinutes: self::CODE_TTL_MINUTES,
            );
        } catch (Throwable $exception) {
            $this->clearCode($normalizedEmail);

            throw $exception;
        }
    }

    /**
     * @return array{email: string, name: string, code: string}|null
     */
    public function getCodeData(string $email): ?array
    {
        $cached = Cache::get($this->verificationCodeKey($email));

        if (is_array($cached)) {
            return $cached;
        }

        if (! is_string($cached)) {
            return null;
        }

        $decoded = json_decode($cached, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function verifyCode(string $email, string $code): ?string
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $data = $this->getCodeData($normalizedEmail);

        if (! isset($data['code']) || ! is_string($data['code'])) {
            return null;
        }

        if (! hash_equals($data['code'], $code)) {
            return null;
        }

        $token = Str::random(64);
        $tokenHash = hash('sha256', $token);
        $expiresAt = now()->addMinutes(self::REGISTRATION_TOKEN_TTL_MINUTES);

        Cache::put($this->registrationTokenKey($token), $normalizedEmail, $expiresAt);
        Cache::put($this->verifiedEmailKey($normalizedEmail), $tokenHash, $expiresAt);
        Cache::forget($this->verificationCodeKey($normalizedEmail));

        return $token;
    }

    public function isRegistrationTokenValid(string $email, string $token): bool
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $tokenHash = hash('sha256', $token);
        $verifiedTokenHash = Cache::get($this->verifiedEmailKey($normalizedEmail));
        $verifiedEmail = Cache::get($this->registrationTokenKey($token));

        return is_string($verifiedTokenHash)
            && is_string($verifiedEmail)
            && hash_equals($verifiedTokenHash, $tokenHash)
            && hash_equals($verifiedEmail, $normalizedEmail);
    }

    public function consumeRegistrationToken(string $email, string $token): void
    {
        $normalizedEmail = $this->normalizeEmail($email);

        Cache::forget($this->registrationTokenKey($token));
        Cache::forget($this->verifiedEmailKey($normalizedEmail));
    }

    public function clearCode(string $email): void
    {
        Cache::forget($this->verificationCodeKey($email));
    }

    private function generateCode(string $email, string $name): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(
            $this->verificationCodeKey($email),
            [
                'email' => $this->normalizeEmail($email),
                'name' => $name,
                'code' => $code,
            ],
            now()->addMinutes(self::CODE_TTL_MINUTES),
        );

        return $code;
    }

    private function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }

    private function verificationCodeKey(string $email): string
    {
        return 'verify:email:'.$this->normalizeEmail($email);
    }

    private function verifiedEmailKey(string $email): string
    {
        return 'verified:email:'.$this->normalizeEmail($email);
    }

    private function registrationTokenKey(string $token): string
    {
        return 'verify:registration:'.hash('sha256', $token);
    }
}
