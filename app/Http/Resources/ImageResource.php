<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ImageResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'raw_hash' => $this->resource->raw_hash,
            'hash' => $this->resource->hash,
            'size' => $this->resource->size,
            'size_formatted' => $this->resource->human_readable_size,
            'original_size' => $this->resource->original_size,
            'original_size_formatted' => $this->resource->original_human_readable_size,
            'url' => Storage::disk('s3')
                ->temporaryUrl($this->resource->original_path ?? $this->resource->path, now()->addMinutes(5)),
        ];
    }
}
