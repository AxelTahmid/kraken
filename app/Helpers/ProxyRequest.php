<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Request;

class ProxyRequest
{
    public function grantPasswordToken(string $email, string $password)
    {
        $params = [
            'grant_type' => 'password',
            'username' => $email,
            'password' => $password,
        ];

        return $this->makePostRequest($params);
    }

    public function refreshAccessToken()
    {
        $refreshToken = request()->cookie('refresh_token');

        abort_unless($refreshToken, 403, 'Your refresh token is expired.');

        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];

        return $this->makePostRequest($params);
    }

    protected function makePostRequest(array $params)
    {
        $params = array_merge([
            'client_id' => config('services.passport.password_client_id'),
            'client_secret' => config('services.passport.password_client_secret'),
            'scope' => '*',
        ], $params);

        $proxy = Request::create('oauth/token', 'post', $params);

        dd($proxy);

        $response = json_decode(app()->handle($proxy)->getContent());

        // dd($response);

        $this->setHttpOnlyCookie($response->refresh_token);

        return $response;
    }

    protected function setHttpOnlyCookie(string $refreshToken)
    {
        cookie()->queue(
            'refresh_token',
            $refreshToken,
            14400, // 10 days
            null,
            null,
            false,
            true // httponly
        );
    }
}
