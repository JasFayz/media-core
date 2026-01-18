<?php

namespace App\Models;

use App\Enums\ImageStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'user_id',
        'raw_hash',
        'hash',
        'original_name',
        'original_mime',
        'mime',
        'size',
        'original_size',
        'path',
        'original_path',
        'status',
    ];

    protected $casts = [
        'status' => ImageStatus::class,
    ];

    public function humanReadableSize(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->formatBytes($this->size),
        );
    }

    public function originalHumanReadableSize(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->formatBytes($this->original_size),
        );
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        if ($bytes === null) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
