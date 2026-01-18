<?php

namespace App\Actions\Image;

use App\Enums\ImageStatus;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ListUserImageAction
{
    public function execute(User $user): LengthAwarePaginator
    {
        return $user->images()->where('status', ImageStatus::READY)->paginate();
    }
}
