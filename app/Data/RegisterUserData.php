<?php

namespace App\Data;

use App\Http\Requests\RegisterUserRequest;
use Illuminate\Support\Facades\Hash;
use Spatie\LaravelData\Data;

class RegisterUserData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    )
    {
    }

    public static function fromRequest(RegisterUserRequest $request): RegisterUserData
    {
        return new self(
            $request->name,
            $request->email,
            Hash::make($request->password),
        );
    }
}
