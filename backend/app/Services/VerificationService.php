<?php
namespace App\Services;

use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class VerificationService
{
    public function generateCode(string $email, string $name): string
    {
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
        $cached = Cache::get("verify:email:$email");
        return $cached ? json_decode($cached, true) : null;
    }

    public function generateCodeAndSend(string $email, string $name): void
    {
        $code = $this->generateCode($email, $name);
        Mail::to($email)->queue(new VerificationCodeMail($name, $code));
    }
    public function verifyCode(string $email, string $code): bool
    {
        $stored = Cache::get("verify:email:$email");

        if (!$stored) {
            return false;
        }

        $data = json_decode($stored, true);

        if ($data['code'] != $code) {
            return false;
        }

        // 인증 성공 시 인증된 상태로 별도 캐시 저장
        Cache::put("verified:email:$email", $data, now()->addMinutes(30));
        return true;
    }
    public function clearCode(string $email): void
    {
        Cache::forget("verify:email:$email");
    }
}
