<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class GoogleLoginController extends Controller
{
    public function handle(Request $request)
    {
        $googleToken = $request->input('token');

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($googleToken);

            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName() ?? 'No Name',
                    'password' => bcrypt(Str::random(24)), // 비밀번호 필요 없음
                ]
            );

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'token' => $token,
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid Google token',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
