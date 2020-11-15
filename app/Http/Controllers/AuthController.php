<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use App\User_register_log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Location\Facades\Location;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */

    public function getLog()
    {
        $data = User_register_log::all();
        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'data' => $data
        ],200);
    }

    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'address' => 'required|string',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        $data = $request->all();
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);

        //for send email verification
        // $user->sendEmailVerificationNotification();

        //auto mark as verified
        $user->markEmailAsVerified();

        $location = Location::get();

        $logDaftar = array(
            'nama' => $request['name'],
            'location' => $location->countryName
        );

        $userRegisterLog = User_register_log::create($logDaftar);

        return response()->json([
            'status' => 1,
            'message' => 'Successfully created user!'
        ], 201);
        // $user = new User([
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'address' => $request->address,
        //     'phone' => $request->phone,
        //     'password' => bcrypt($request->password)
        // ]);
        // $user->save();

    }

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
            'password' => 'required|string',
            'device_id' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $validator = Validator::make(['email' => $request['email_or_phone']],['email' => 'required|email']);
        if($validator->passes()){
            $credentials = array(
                'email' => $request['email_or_phone'],
                'password' => $request['password']
            );
        }
        else {
            $credentials = array(
                'phone' => $request['email_or_phone'],
                'password' => $request['password']
            );
        }

        if(!Auth::attempt($credentials))
            return response()->json([
                'status' => 0,
                'message' => 'Unauthorized'
            ], 401);

        $user = $request->user();

        // if(!$user->hasVerifiedEmail()){
        //     $user->sendEmailVerificationNotification();
        //     return response()->json([
        //         "status" => 0,
        //         "message" => "Email verification sent"
        //     ]);
        // }

        $tokenResult = $user->createToken('Personal Access Token',['user-token']);
        $token = $tokenResult->token;
        if ($request->remember_me){
            $token->expires_at = Carbon::now()->addWeeks(1);
        }
        else {
            $token->expires_at = Carbon::now()->addDays(1);
        }
            $token->save();

            $data = User::where('id',request()->user()->id)->first();
            if(!is_null($request['device_id'])){
                $request->validate([
                    'device_id' => 'required'
                ]);
                $data->device_id = $request['device_id'];
            }
            $data->save();

            return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()
        ]);
    }

    public function login_dev(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'keyword' => 'required|string'
        ]);

        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials))
            return response()->json([
                'status' => 0,
                'message' => 'Unauthorized'
            ], 401);

        if(hash('sha256',$request['keyword']) != 'c95f46c7236e806bf134ac4ebc372a8a0313845630ba7072b2ea743f8a030491'){
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = $request->user();
        $tokenResult = $user->createToken('System Access Token',['system-token']);
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(54000);
        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'status' => 1,
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function password(Request $request)
    {
        $data = User::where('id',request()->user()->id)->first();
        $request->validate([
            'password' => 'required|string',
            'old_password' => 'required|string'
        ]);

        if(Hash::check($request['old_password'], $data->password))
        {
            $data->password = bcrypt($request['password']);
            $data->save();
            return response()->json([
                'status' => 1,
                'message' => 'Password updated!'
            ],200);
        }
        else {
            return response()->json([
                'status' => 0,
                'message' => 'Wrong old password!'
            ],200);
        }
    }

    public function update(Request $request)
    {

        $data = User::where('id',request()->user()->id)->first();

        if(!is_null($request['name'])){
            $request->validate([
                'name' => 'required'
            ]);
            $data->name = $request['name'];
        }

        if(!is_null($request['email'])){
            $request->validate([
                'email' => 'required|email|unique:users,email'
            ]);
            $data->email = $request['email'];
        }

        if(!is_null($request['address'])){
            $request->validate([
                'address' => 'required'
            ]);
            $data->address = $request['address'];
        }

        if(!is_null($request['phone'])){
            $request->validate([
                'phone' => 'required|unique:users,phone'
            ]);
            $data->phone = $request['phone'];
        }

        $data->save();
        return response()->json([
            'status' => 1,
            'message' => 'Resource updated!'
        ],200);
    }
}
