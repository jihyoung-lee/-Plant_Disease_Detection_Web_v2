<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Services\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{
    protected $verification;

    // 생성자 주입
    public function __construct(VerificationService $verification)
    {
        $this->verification = $verification;
    }

    public function notification(Request $request)
    {
        $validated = $request->validate(['email' => 'required|email|max:255']);
        try{
            $email = $request->input("email");
            $name = $request->input('name');

            // 중복 확인
            if (User::where('email', $email)->exists()) {
                return response()->json(['message' => '이미 사용 중인 이메일입니다.'], 422);
            }
            $this->verification->generateCode($email, $name);

            return response()->json([
                'success' => true,
                'massage' => '인증번호가 이메일로 전송되었습니다',
            ]);
        } catch (\Exception $e) {
            Log::error('인증번호 전송 실패 :' .$e->getMessage(), [
                'email' => $validated['email'],
                'error' => $e->getTrace()
            ]);

            return response()->json([
                'success' => false,
                'message' => '인증번호 전송에 실패했습니다. 잠시 후 다시 시도해주세요.'
            ], 500);
        }
    }
    public function verify(Request $request)
    {
        $email = $request->input('email');
        $code = $request->input('code');

        $stored = Cache::get("verify:email:$email");

        if (!$stored) {
            return response()->json([
                'error' => '잘못된 인증코드 또는 만료된 코드입니다.'
            ], 401);
        }

        $data = json_decode($stored, true);

        if ($data['code'] != $code) {
            return response()->json(['message' => '인증코드가 올바르지 않습니다.'], 400);
        }

        Cache::put("verified:email:$email", $data, now()->addMinutes(30));

        return response()->json([
            'message' => '이메일 인증이 완료되었습니다.'
        ]);
    }

    public function resend(Request $request)
    {
        $validated = $this->check_email($request->email);

        $user = User::where('email', $validated)->firstOrFail();
        $this->verification->generateCode($email, $name);
        Mail::to($user->email)->queue(
            new VerificationCodeMail($user, $code)
        );


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

        //캐시 저장 10초
        Cache::put($cacheKey, $response, now()->addSeconds(10));

        return response()->json($response);
    }
}
