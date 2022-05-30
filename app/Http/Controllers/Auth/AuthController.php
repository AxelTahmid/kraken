<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\ProxyRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $proxy;

    public function __construct(ProxyRequest $proxy)
    {
        $this->proxy = $proxy;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }


        $request['password'] = Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);

        $user = User::create($request->toArray());

        $response = $this->proxy->grantPasswordToken(
            $user->email,
            request('password')
        );

        return response([
            'token' => $response->access_token,
            'expiresIn' => $response->expires_in,
            'message' => 'Your account has been created',
        ], 201);

        // $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        // $response = ['token' => $token];

        // return response($response, 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $user = User::where('email', $request->email)->first();

        abort_unless($user, 404, 'This combination does not exists.');
        abort_unless(
            Hash::check(request('password'), $user->password),
            403,
            'This combination does not exists.'
        );

        $response = $this->proxy
            ->grantPasswordToken(request('email'), request('password'));

        return response([
            'token' => $response->access_token,
            'expiresIn' => $response->expires_in,
            'message' => 'You have been logged in',
        ], 200);

        // if ($user) {
        //     if (Hash::check($request->password, $user->password)) {
        //         $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        //         $response = ['token' => $token];
        //         return response($response, 200);
        //     } else {
        //         $response = ["message" => "Password is Incorrect"];
        //         return response($response, 422);
        //     }
        // } else {
        //     $response = ["message" => 'User does not exist'];
        //     return response($response, 422);
        // }
    }

    public function refreshToken()
    {
        $resp = $this->proxy->refreshAccessToken();

        return response([
            'token' => $resp->access_token,
            'expiresIn' => $resp->expires_in,
            'message' => 'Token has been refreshed.',
        ], 200);
    }

    public function logout()
    {
        $token = request()->user()->token();
        $token->delete();

        cookie()->queue(cookie()->forget('refresh_token'));

        return response([
            'message' => 'You have been successfully logged out',
        ], 200);

        // $token = $request->user()->token();
        // $token->revoke();
        // $response = ['message' => 'You have been successfully logged out!'];
        // return response($response, 200);
    }
}
