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
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh() {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json(['error' => '토큰 없음'], 401);
            }

            $newToken = JWTAuth::refresh($token);
            return $this->respondWithToken($newToken);
        } catch (\Exception $e) {
            return response()->json(['error' => '리프레시 실패', 'message' => $e->getMessage()], 500);
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
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }
}

