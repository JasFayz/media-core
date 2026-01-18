<?php

namespace App\Http\Controllers;

use App\Actions\Image\DeleteImageAction;
use App\Actions\Image\ListUserImageAction;
use App\Actions\Image\StoreImageAction;
use App\Data\StoreImageData;
use App\Exceptions\ImageTooLargeException;
use App\Exceptions\InvalidImageTypeException;
use App\Http\Requests\StoreImageRequest;
use App\Http\Resources\ImageResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ImageController extends BaseApiController
{
    public function index(ListUserImageAction $listUserImageAction)
    {
        $images = $listUserImageAction->execute(auth()->user());

        $meta = [];
        if ($images->isNotEmpty()) {
            $meta = [
                'current_page' => $images->currentPage(),
                'last_page' => $images->lastPage(),
                'per_page' => $images->perPage(),
                'total' => $images->total(),
            ];
        }

        return $this->success(ImageResource::collection($images), meta: $meta);
    }

    /**
     * @throws ImageTooLargeException
     * @throws InvalidImageTypeException
     * @throws \Throwable
     */
    public function store(StoreImageRequest $request, StoreImageAction $storeImageAction)
    {
        return $this->success(ImageResource::make($storeImageAction->execute(StoreImageData::fromRequest($request))));
    }

    public function show(string $id)
    {
        $userId = auth()->id();

        $image = Cache::remember(
            "image:{$userId}:{$id}",
            now()->addMinutes(5),
            fn() => auth()->user()->images()->findOrFail($id)
        );

        return $this->success(ImageResource::make($image));
    }

    /**
     * @throws \Throwable
     */
    public function destroy(string $id, DeleteImageAction $deleteImageAction)
    {
        return $this->success($deleteImageAction->execute($id));
    }
}
