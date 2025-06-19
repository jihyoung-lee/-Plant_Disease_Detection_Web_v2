<?php

namespace App\Http\Controllers\Auth;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6'
        ]);

        $user = User::where('email', $request->email)
            ->where('verification_code', $request->code)
            ->where('code_expires_at', '>', now())
            ->first();

        if (!$user) {
            return response()->json([
                'error' => '잘못된 인증코드 또는 만료된 코드입니다.'
            ], 401);
        }

        $user->update([
            'verification_code' => null,
            'code_expires_at' => null,
            'email_verified_at' => now()
        ]);

        $token = auth()->login($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->firstOrFail();
        $code = $user->generateVerificationCode();
        Mail::to($user->email)->send(new VerificationCodeMail($code));

        return response()->json([
            'message' => '새 인증코드가 전송되었습니다.'
        ]);
    }
}