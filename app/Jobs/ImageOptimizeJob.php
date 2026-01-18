<?php

namespace App\Jobs;

use App\Actions\Image\OptimizeImageAction;
use App\Enums\ImageStatus;
use App\Models\Image;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ImageOptimizeJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public string $imageId)
    {
    }

    /**
     * @throws \Throwable
     */
    public function handle(OptimizeImageAction $optimizeImageAction): void
    {
        $optimizeImageAction->execute($this->imageId);
    }

    public function failed(\Throwable $e): void
    {
        Image::where('id', $this->imageId)
            ->where('status', ImageStatus::PENDING)
            ->update(['status' => ImageStatus::FAILED]);
    }
}
