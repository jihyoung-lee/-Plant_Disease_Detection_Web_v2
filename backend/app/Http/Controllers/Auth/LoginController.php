<?php
namespace App\Http\Controllers\Auth;
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => '이메일 혹은 비밀번호가 올바르지 않습니다.',
            ], 401);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => '로그인 성공',
            'user' => Auth::user(),
        ]);
    }
    public function logout(Request $request)
    {
        Auth::guard('web')->logout(); // 로그아웃
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => '로그아웃 완료']);
    }
}

