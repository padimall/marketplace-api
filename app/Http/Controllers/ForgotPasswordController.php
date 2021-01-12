<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Helper\Helper;

class ForgotPasswordController extends Controller
{
    public function forgot(Request $request) {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);
        $credentials['email'] = $request['email'];

        $response = Password::sendResetLink($credentials);

        return response()->json([
            "status" => 1,
            "message" => 'Reset password link sent on your email!'
        ]);
    }

    public function changePassword(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);

        $credentials = $request->all();

        $reset_password_status = Password::reset($credentials, function ($user, $password) {
            $user->password = bcrypt($password);
            $user->save();
        });

        if ($reset_password_status == Password::INVALID_TOKEN) {
            return response()->json([
                "status" => 0,
                "message" => "Invalid token provided"
            ], 400);
        }

        return response()->json([
            "status" => 1,
            "message" => "Password has been successfully changed"
        ],200);
    }
}
