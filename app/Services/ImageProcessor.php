<?php

namespace App\Services;

use Intervention\Image\ImageManager;

class ImageProcessor
{
    public function __construct(
        private ImageManager $manager
    )
    {
    }

    public function optimizeToWebp(
        string $input,
        string $output,
        int    $maxWidth = 2048,
        int    $quality = 85
    ): void
    {
        $img = $this->manager->read($input)->scaleDown($maxWidth);

        if ($img->width() > $maxWidth) {
            $img->scaleDown($maxWidth);
        }
        $img->toWebp($quality)
            ->save($output);
    }
}
