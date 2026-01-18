<?php

namespace App\Actions\Auth;

use App\Data\RegisterUserData;
use App\Models\User;

class RegisterUserAction
{
    public function execute(RegisterUserData $data): string
    {
        $user = User::create($data->toArray());

        return $user->createToken('api')->plainTextToken;
    }
}
