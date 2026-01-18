<?php

namespace App\Data;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

class StoreImageData extends Data
{
    public function __construct(
        public User         $user,
        public UploadedFile $file,
    )
    {
    }

    public static function fromRequest(Request $request): StoreImageData
    {
        return new self(
            auth()->user(),
            $request->file('file'),
        );
    }
}
