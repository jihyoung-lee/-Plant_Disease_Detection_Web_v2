<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    protected $fillable = [
        'name', 'email', 'password', 'verification_code', 'code_expires_at'
    ];

    // JWT 메서드
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }

    // 인증번호 생성 메서드
    public function generateVerificationCode()
    {
        $this->update([
            'verification_code' => rand(100000, 999999),
            'code_expires_at' => now()->addMinutes(30)
        ]);
        return $this->verification_code;
    }
}