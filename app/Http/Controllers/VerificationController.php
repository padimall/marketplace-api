<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class VerificationController extends Controller
{
    public function verify(Request $request) {
        if (!$request->hasValidSignature()) {
            return response()->json(["msg" => "Invalid/Expired url provided."], 401);
        }

        $user = User::findOrFail($request['id']);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        response()->json([
            'status' => 1,
            "message" => 'Email verified!'
        ]);
    }

}
