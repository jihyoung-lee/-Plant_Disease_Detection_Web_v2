<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Mail\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly VerificationService $verification
    ) {
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'verification_token' => ['required', 'string', 'size:64'],
        ]);

        if (!$this->verification->isRegistrationTokenValid(
            $validated['email'],
            $validated['verification_token']
        )) {
            throw ValidationException::withMessages([
                'email' => ['이메일 인증이 필요하거나 인증 정보가 만료되었습니다.'],
            ]);
        }

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $this->verification->consumeRegistrationToken(
                $validated['email'],
                $validated['verification_token']
            );

            return response()->json([
                'message' => '회원가입이 완료되었습니다.',
                'user' => $user,
            ], 201);

        } catch (\Exception $e) {
            Log::error('회원가입 실패: ' . $e->getMessage());

            return response()->json([
                'message' => '회원가입 처리 중 오류가 발생했습니다. 나중에 다시 시도해주세요.'
            ], 500);
        }
    }
}
