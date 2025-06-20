<?php

namespace App\Http\Controllers\Auth;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $validated = $this->check_email($request->email);

        $user = User::where('email', $validated)->firstOrFail();
        $code = $user->generateVerificationCode();
        Mail::to($user->email)->send(new VerificationCodeMail($code));

        return response()->json([
            'message' => '새 인증코드가 전송되었습니다.'
        ]);
    }

    public function check_email(Request $request){

        // 입력값 검증
        $validated = $request->validate([
            'email' => 'required|email:rfc,dns|max:255'
        ]);

        // 캐시 키 생성 (동일 이메일 요청 시)
        $cacheKey = 'email_check:' . md5($validated['email']);

        // 캐시 확인
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // 중복 검사
        $exists = User::where('email', $validated['email'])->exists();

        // 응답 반환
        $response = [
            'exists' => $exists,
            'message' => $exists ?
                '이미 사용 중인 이메일입니다' :
                '사용 가능한 이메일입니다',
        ];

        //캐시 저장 60초
        Cache::put($cacheKey, $response, now()->addSeconds(60));

        return response()->json($response);
    }
}