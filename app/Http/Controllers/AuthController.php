<?php

namespace App\Http\Controllers;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Data\RegisterUserData;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Http\Request;

class AuthController extends BaseApiController
{
    public function register(RegisterUserRequest $request, RegisterUserAction $registerUserAction)
    {
        return $this->success($registerUserAction->execute(RegisterUserData::fromRequest($request)));
    }

    public function login(LoginRequest $request, LoginUserAction $loginUserAction)
    {
        return $this->success($loginUserAction->execute(...$request->only('email', 'password')));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success();
    }
}
