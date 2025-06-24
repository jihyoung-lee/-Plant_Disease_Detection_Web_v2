<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Services\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    protected $verification;

    public function __construct(VerificationService $verification)
    {
        $this->verification = $verification;
    }

    public function notification(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'required|string|max:255',
        ]);

        try {
            if (User::where('email', $validated['email'])->exists()) {
                return response()->json(['message' => '이미 사용 중인 이메일입니다.'], 422);
            }

            $this->verification->generateCodeAndSend($validated['email'], $validated['name']);

            return response()->json([
                'success' => true,
                'message' => '인증번호가 이메일로 전송되었습니다.',
            ]);
        } catch (\Exception $e) {
            Log::error('인증번호 전송 실패: ' . $e->getMessage(), [
                'email' => $validated['email'],
                'error' => $e->getTrace()
            ]);

            return response()->json([
                'success' => false,
                'message' => '인증번호 전송에 실패했습니다. 잠시 후 다시 시도해주세요.'
            ], 500);
        }
    }

    public function resend(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'required|string|max:255',
        ]);

        $this->verification->generateCodeAndSend($validated['email'], $validated['name']);

        return response()->json([
            'message' => '새 인증코드가 전송되었습니다.'
        ]);
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'code' => 'required|string|size:6'
        ]);

        $isValid = $this->verification->verifyCode($validated['email'], $validated['code']);

        if (!$isValid) {
            return response()->json(['message' => '인증코드가 올바르지 않거나 만료되었습니다.'], 400);
        }

        return response()->json([
            'message' => '이메일 인증이 완료되었습니다.'
        ]);
    }

    public function check_email(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email:rfc,dns|max:255'
        ]);

        $cacheKey = 'email_check:' . md5($validated['email']);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $exists = User::where('email', $validated['email'])->exists();

        $response = [
            'exists' => $exists,
            'message' => $exists ? '이미 사용 중인 이메일입니다' : '사용 가능한 이메일입니다',
        ];

        Cache::put($cacheKey, $response, now()->addSeconds(10));

        return response()->json($response);
    }
}
