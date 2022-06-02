<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginPostRequest;
use App\Http\Requests\RegisterPostRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginPostRequest $request)
    {
        $credentials = $request->validated();
        // print_r($credentials);die;  

        if (!Auth::attempt($credentials)) {
            return $this->errorResponse('Incorrect Credentials', 401);
        }

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');

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

    public function register(RegisterPostRequest $request)
    {
        $form_data = $request->validated();
        $form_data['password'] = Hash::make($request['password']);

        $user = User::create($form_data);

        return $this->successResponse(
            $user,
            'Registration Successfull',
            201
        );
    }

    public function logout()
    {
        request()->user()->token()->delete();
        return $this->successResponse(
            '',
            'Logout Successfull',
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
