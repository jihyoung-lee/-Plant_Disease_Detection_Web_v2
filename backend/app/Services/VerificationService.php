<?php
namespace App\Services;

use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;

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
