<?php
namespace App\Http\Controllers\Auth;
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\App;
    use Illuminate\Support\Facades\Auth;
    use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }
    public function logout()
    {
        auth('api')->logout();

        if (App::environment('production')) {
            return response()->json(['message' => 'Successfully logged out'])
                ->cookie('access_token', '', -1);
        }

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    protected function respondWithToken($token)
    {

        $expires = auth('api')->factory()->getTTL() * 60;

        if (App::environment() == 'production') {
            // 운영환경 쿠키에 토큰저장
            return response()->json(['message' => 'Login successful'])
                ->cookie(
                    'access_token',           // 쿠키 이름
                    $token,                   // 쿠키 값 (JWT)
                    $expires / 60,            // 분 단위
                    '/',                      // path
                    null,                     // domain
                    true,                     // secure
                    true,                     // httpOnly
                    false,                    // raw
                    'Strict'                  // SameSite
                );
        } else {
            // 개발환경: JSON에 토큰 포함
            return response()->json([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $expires
            ]);
        }
    }
}

