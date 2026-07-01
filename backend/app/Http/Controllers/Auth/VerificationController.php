<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckEmailRequest;
use App\Http\Requests\SendVerificationCodeRequest;
use App\Http\Requests\VerifyEmailCodeRequest;
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

    public function notification(SendVerificationCodeRequest $request)
    {
        $validated = $request->validated();
        try {
            if (User::where('email', $validated['email'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => '이미 사용 중인 이메일입니다.'], 422);
            }

            $this->verification->generateCodeAndSend($validated['email'], $validated['name']);

            return response()->json([
                'success' => true,
                'message' => '인증번호가 이메일로 전송되었습니다.',
            ]);
        } catch (\Exception $e) {
            Log::error('인증번호 전송 실패: ' . $e->getMessage(), [
                'success' => false,
                'email' => $validated['email'],
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '인증번호 전송에 실패했습니다. 잠시 후 다시 시도해주세요.'
            ], 500);
        }
    }

    public function resend(SendVerificationCodeRequest $request)
    {
        try{
            $validated = $request->validated();

        $this->verification->generateCodeAndSend($validated['email'], $validated['name']);

        return response()->json([
            'success' => true,
            'message' => '새 인증코드가 전송되었습니다.'
        ]);
        }catch (\Exception $e){
            Log::error('인증번호 전송 실패: ' . $e->getMessage(), [
                'success' => false,
                'email' => $request->input('email'),
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => '인증번호 전송에 실패했습니다. 잠시 후 다시 시도해주세요.'
            ], 500);
        }
    }

    public function verify(VerifyEmailCodeRequest $request)
    {
        $validated = $request->validated();
        $verificationToken = $this->verification->verifyCode(
            $validated['email'],
            $validated['code']
        );

        if ($verificationToken === null) {
            return response()->json([
                'success' => false,
                'message' => '인증코드가 올바르지 않거나 만료되었습니다.'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => '이메일 인증이 완료되었습니다.',
            'verification_token' => $verificationToken,
            'expires_in' => 1800,
        ]);
    }

    public function checkEmail(CheckEmailRequest $request)
    {
        $validated = $request->validated();

        $cacheKey = 'email_check:' . sha1($validated['email']);

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey), 200);
        }

        $exists = User::where('email', $validated['email'])->exists();

        $response = [
            'success' => true,
            'exists' => $exists,
            'message' => $exists ? '이미 사용 중인 이메일입니다' : '사용 가능한 이메일입니다',
        ];

        Cache::put($cacheKey, $response, now()->addSeconds(10));

        return response()->json($response, 200);
    }
}
