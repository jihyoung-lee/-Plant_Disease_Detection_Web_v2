<?php

namespace App\Services\Mail;

use App\Exceptions\Mail\VerificationMailException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class MailerSendClient
{
    public function sendVerificationCode(
        string $email,
        string $name,
        string $code,
        int $expiresInMinutes,
    ): void {
        $apiKey = config('services.mailersend.key');
        $endpoint = config('services.mailersend.endpoint');
        $fromAddress = config('services.mailersend.from.address');
        $fromName = config('services.mailersend.from.name');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new VerificationMailException('MailerSend API 키가 설정되지 않았습니다.');
        }

        if (! is_string($endpoint) || trim($endpoint) === '') {
            throw new VerificationMailException('MailerSend API 주소가 설정되지 않았습니다.');
        }

        if (! is_string($fromAddress) || trim($fromAddress) === '') {
            throw new VerificationMailException('인증 메일 발신 주소가 설정되지 않았습니다.');
        }

        if (! is_string($fromName) || trim($fromName) === '') {
            throw new VerificationMailException('인증 메일 발신자 이름이 설정되지 않았습니다.');
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->connectTimeout((int) config('services.mailersend.connect_timeout', 5))
                ->timeout((int) config('services.mailersend.timeout', 10))
                ->post($endpoint, [
                    'from' => [
                        'email' => $fromAddress,
                        'name' => $fromName,
                    ],
                    'to' => [
                        [
                            'email' => $email,
                            'name' => $name,
                        ],
                    ],
                    'subject' => '이메일 인증번호 안내',
                    'text' => $this->verificationMessage(
                        name: $name,
                        code: $code,
                        expiresInMinutes: $expiresInMinutes,
                    ),
                ]);
        } catch (ConnectionException $exception) {
            throw new VerificationMailException(
                'MailerSend 서버에 연결할 수 없습니다.',
                previous: $exception,
            );
        }

        if ($response->status() !== 202) {
            throw new VerificationMailException(
                "MailerSend가 메일 요청을 거부했습니다. 상태 코드: {$response->status()}"
            );
        }
    }

    private function verificationMessage(
        string $name,
        string $code,
        int $expiresInMinutes,
    ): string {
        return "안녕하세요 {$name}님,\n\n"
            . "인증번호는 [{$code}] 입니다.\n\n"
            . "{$expiresInMinutes}분 이내에 입력해 주세요.";
    }
}
