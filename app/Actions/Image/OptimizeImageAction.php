<?php

namespace App\Actions\Image;

use App\Enums\ImageStatus;
use App\Models\Image;
use App\Services\ImageProcessor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class OptimizeImageAction
{
    public function __construct(private ImageProcessor $imageProcessor)
    {
    }

    /**
     * @throws \Throwable
     */
    public function execute(string $imageId): void
    {
        $image = Image::findOrFail($imageId);

        if ($image->status !== ImageStatus::PENDING) {
            return;
        }

        $this->tempDirectory();

        $tmpOriginal = storage_path('app/tmp/' . Str::uuid());
        $tmpWebp = storage_path('app/tmp/' . Str::uuid() . '.webp');

        $stream = Storage::disk('s3')->readStream($image->original_path);

        if (!$stream) {
            throw new RuntimeException('Unable to read original image');
        }

        $writeStream = fopen($tmpOriginal, 'w');
        stream_copy_to_stream($stream, $writeStream);
        fclose($stream);
        fclose($writeStream);

        $this->optimize($tmpOriginal, $tmpWebp);

        $hash = hash_file('sha256', $tmpWebp);

        $filename = $hash . '.webp';

        $path = 'converted/' . substr($hash, 0, 2) . '/' . $filename;

        if (!Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->put($path, fopen($tmpWebp, 'r'));
        }
        $size = Storage::disk('s3')->size($path);

        $this->update($imageId, $hash, $path, $size);

        @unlink($tmpOriginal);
        @unlink($tmpWebp);
        $image->refresh();
        if (
            $image->status === ImageStatus::READY
            && $image->path
            && Storage::disk('s3')->exists($image->path)
        ) {
            $isUsedByMore = Image::where('original_path', $image->original_path)
                ->where('id', '!=', $image->id)
                ->exists();

            if (!$isUsedByMore) {
                Storage::disk('s3')->delete($image->original_path);
            }

            $image->update([
                'original_path' => null,
            ]);
        }
        Cache::forget("image:{$image->user_id}:{$image->id}");
    }

    protected function tempDirectory(): string
    {
        $tmpDir = storage_path('app/tmp');

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        return $tmpDir;
    }

    protected function optimize(string $tmpOriginal, string $tmpWebp): void
    {
        $this->imageProcessor->optimizeToWebp($tmpOriginal, $tmpWebp);
    }

    /**
     * @throws \Throwable
     */
    protected function update($imageId, $hash, $path, $size): void
    {
        DB::transaction(function () use ($imageId, $hash, $path, $size) {
            $image = Image::lockForUpdate()->findOrFail($imageId);

            if ($image->status !== ImageStatus::PENDING) {
                return;
            }

            $existing = Image::where('hash', $hash)->first();

            if ($existing) {
                $image->update([
                    'hash' => $existing->hash,
                    'path' => $existing->path,
                    'mime' => $existing->mime,
                    'size' => $existing->size,
                    'status' => ImageStatus::READY,
                ]);
            } else {
                $image->update([
                    'hash' => $hash,
                    'path' => $path,
                    'mime' => 'image/webp',
                    'size' => $size,
                    'status' => ImageStatus::READY,
                ]);
            }
        });
    }
}
