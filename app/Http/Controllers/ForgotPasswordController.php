<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function forgot() {
        $credentials = request()->validate(['email' => 'required|email']);

        Password::sendResetLink($credentials);

        return response()->json([
            "msg" => 'Reset password link sent on your email id.',
            'cs' => $credentials
        ]);
    }

    public function changePassword(Request $request) {
        // $request->validate([
        //     "email" => "required|email|exists:users,email"
        // ]);
        
        // $credentials['email'] = $request['email'];

        // Password::sendResetLink($credentials);

        $credentials['email'] = $request['email'];

        Password::sendResetLink($credentials);

        return response()->json([
            "msg" => 'Reset password link sent on your email id.',
            'cs' => $credentials
        ]);
    }
}
