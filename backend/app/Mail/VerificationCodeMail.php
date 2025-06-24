<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;       // 공개 프로퍼티로 선언
    public $verificationCode;

    /**
     * Create a new message instance.
     */
    public function __construct(string $user, string $verificationCode)
    {
        $this->user = $user;  // 프로퍼티 할당
        $this->verificationCode = $verificationCode;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->view('emails.verification')
            ->subject('이메일 인증 코드')
            ->with([
                'user' => $this->user,
                'verificationCode' => $this->verificationCode
            ]);
    }
}
