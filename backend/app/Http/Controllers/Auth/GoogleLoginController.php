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
            Log::error('ID 토큰 검증 실패: ' . $response->body());
            return response()->json(['error' => 'Invalid Google token'], 401);
        }

        $googleUser = $response->json();

        // aud는 이 토큰이 어느 앱을 위해 발급됐는지
        if (($googleUser['aud'] ?? null) !== config('services.google.client_id')) {
            return response()->json(['error' => 'Invalid Google token audience'], 401);
        }

        // 이메일 인증 검증
        if (($googleUser['email_verified'] ?? null) !== 'true') {
            return response()->json(['error' => 'Google email is not verified'], 401);
        }

        $user = User::firstOrCreate(
            ['email' => $googleUser['email']],
            [
                'name' => $googleUser['name'],
                'password' => bcrypt(Str::random(24)),
            ]
        );

        $user->update([
            'name' => $googleUser['name'],
            'google_id' => $googleUser['sub'],
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

}
