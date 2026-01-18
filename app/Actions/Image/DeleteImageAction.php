<?php

namespace App\Actions\Image;

use App\Models\Image;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteImageAction
{
    /**
     * @throws \Throwable
     */
    public function execute(string $imageId)
    {
        $path = null;
        $originalPath = null;

        $deleted = DB::transaction(function () use ($imageId, &$path, &$originalPath) {
            $image = auth()->user()->images()
                ->lockForUpdate()
                ->findOrFail($imageId);

            $path = $image->path;
            $originalPath = $image->original_path;

            return $image->delete();
        });


        if ($path) {
            $stillUsed = Image::where('path', $path)->exists();

            if (!$stillUsed) {
                Storage::disk('s3')->delete($path);
            }
        }

        if ($originalPath) {
            $originalStillUsed = Image::where('original_path', $originalPath)->exists();

            if (!$originalStillUsed) {
                Storage::disk('s3')->delete($originalPath);
            }
        }
        $userId = auth()->id();
        Cache::forget("image:{$userId}:{$imageId}");

        return $deleted;
    }

}
