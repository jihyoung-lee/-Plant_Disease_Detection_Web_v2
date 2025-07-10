<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class GoogleLoginController extends Controller
{
    public function handle(Request $request)
    {
        $idToken = $request->input('token');

        // 구글 ID 토큰 검증용 endpoint
        $verifyUrl = "https://oauth2.googleapis.com/tokeninfo?id_token={$idToken}";

        $response = Http::get($verifyUrl);

        if (!$response->ok()) {
            Log::error('❌ ID 토큰 검증 실패: ' . $response->body());
            return response()->json(['error' => 'Invalid Google token'], 401);
        }

        $googleUser = $response->json();

        $user = User::updateOrCreate(
            ['email' => $googleUser['email']],
            [
                'name' => $googleUser['name'] ?? 'No Name',
                'password' => bcrypt(Str::random(24)),
            ]
        );

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

}
