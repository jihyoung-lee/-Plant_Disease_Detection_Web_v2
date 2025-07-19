<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\VerificationCodeMail;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{use Illuminate\Support\Facades\Log;
    use Illuminate\Database\QueryException;

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email', // 이메일 중복 방지
            'password' => 'required|min:8|confirmed'
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'message' => '회원가입이 완료되었습니다.',
                'user' => $user
            ], 201);

        } catch (QueryException $e) {
            Log::error('회원가입 실패: ' . $e->getMessage());

            return response()->json([
                'message' => '회원가입 처리 중 오류가 발생했습니다. 나중에 다시 시도해주세요.'
            ], 500);
        }
    }

}
