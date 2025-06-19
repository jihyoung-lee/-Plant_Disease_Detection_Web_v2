<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\VerificationCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $code = $user->generateVerificationCode();
        Mail::to($user->email)->send(new VerificationCodeMail($user, $code));

        return response()->json([
            'message' => '회원가입 성공! 이메일로 인증코드가 전송되었습니다.'
        ], 201);
    }
}