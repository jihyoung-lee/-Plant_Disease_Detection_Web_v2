<?php
namespace App\Http\Controllers\Auth;
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Login failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => '로그아웃 완료'])
                ->cookie('refresh_token', '', -1); // 쿠키 삭제
        } catch (\Exception $e) {
            return response()->json(['error' => '로그아웃 실패', 'message' => $e->getMessage()], 500);
        }
    }

    public function refresh() {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json([
                'token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token refresh failed', 'message' => $e->getMessage()], 401);
        }
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    protected function respondWithToken($token) {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (int) JWTAuth::factory()->getTTL() * 60
        ])->cookie(
            'access_token',       // 쿠키 이름
            $token,
            60 * 24 * 7,           // 유효기간 (분) → 7일
            '/',                   // 경로
            null,                  // 도메인 (기본: 현재 도메인)
            false,                  // Secure (https에서만)
            true,                  // HttpOnly (JS에서 접근 불가)
            false,                 // raw
            null              // SameSite 옵션
        );
    }
}

