<?php

namespace App\Actions\Image;

use App\Data\StoreImageData;
use App\Enums\ImageStatus;
use App\Exceptions\ImageTooLargeException;
use App\Exceptions\InvalidImageTypeException;
use App\Jobs\ImageOptimizeJob;
use App\Models\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StoreImageAction
{
    private const MAX_SIZE = 5 * 1024 * 1024;
    private const ALLOWED_MIME = [
        'image/jpeg',
        'image/png',
    ];

    /**
     * @throws ImageTooLargeException
     * @throws InvalidImageTypeException
     * @throws \Exception
     * @throws \Throwable
     */
    public function execute(StoreImageData $data)
    {
        $file = $data->file;
        $this->checkImageValidation($file);

        $rawHash = substr(hash_file('sha256', $file->getRealPath()), 0, 32);

        [$image, $isNew] = DB::transaction(function () use ($data, $rawHash) {

            $existing = Image::where('user_id', $data->user->id)
                ->where('raw_hash', $rawHash)
                ->first();

            if ($existing) {
                return [$existing, false];
            }

            $image = Image::create([
                'user_id' => $data->user->id,
                'raw_hash' => $rawHash,
                'original_name' => $data->file->getClientOriginalName(),
                'original_mime' => $data->file->getMimeType(),
                'original_size' => $data->file->getSize(),
                'status' => ImageStatus::PENDING,
            ]);

            return [$image, true];
        });

        if (!$isNew) {
            return $image;
        }
        try {
            $directory = 'originals/' . substr($rawHash, 0, 2);
            $extension = match ($file->getMimeType()) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
            };
            $filename = $rawHash . '.' . $extension;
            $path = $directory . '/' . $filename;

            if (Storage::disk('s3')->exists($path)) {
                $originalPath = $path;
            } else {
                $originalPath = Storage::disk('s3')
                    ->putFileAs(
                        $directory,
                        $file,
                        $filename
                    );
            }

            $image->update(['original_path' => $originalPath]);
            ImageOptimizeJob::dispatch($image->id)->afterCommit();

            return $image;
        } catch (\Exception $exception) {
            $image->update([
                'status' => ImageStatus::FAILED,
            ]);
            if (isset($originalPath)) {
                Storage::disk('s3')->delete($originalPath);
            }
            logger()->error('Image upload failed', [
                'user_id' => $data->user->id,
                'raw_hash' => $rawHash,
                'error' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    /**
     * @throws ImageTooLargeException
     * @throws InvalidImageTypeException
     */
    protected function checkImageValidation($file): void
    {
        if ($file->getSize() > self::MAX_SIZE) {
            throw new ImageTooLargeException();
        }

        if (!in_array($file->getMimeType(), self::ALLOWED_MIME, true)) {
            throw new InvalidImageTypeException();
        }
    }
}
