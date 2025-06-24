<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;

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

    public function clearCode(string $email): void
    {
        Cache::forget("verify:email:$email");
    }
}
