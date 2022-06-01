<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponseTrait;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->all(), 422);
        }

        $credentials = request(['email', 'password']);
        // print_r($credentials);die;  

        if (!Auth::attempt($credentials)) {
            return $this->errorResponse('Incorrect Credentials', 401);
        }

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }

        $token->save();

        $data = [
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()
        ];

        return $this->successResponse(
            $data,
            'Login Sucessful!',
            200
        );
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->all(), 422);
        }

        $request['password'] = Hash::make($request['password']);

        $user = User::create($request->toArray());

        return $this->successResponse(
            $user,
            'Successfully created user!',
            201
        );
    }

    public function logout()
    {
        request()->user()->token()->revoke();

        return $this->successResponse(
            '',
            'Logout Successfull !',
            200
        );
    }

    public function user(Request $request)
    {
        return $this->successResponse(
            $request->user(),
            'Active User Fetched!',
            200
        );
    }
}
