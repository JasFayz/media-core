<?php

namespace App\Models;

use App\Enums\ImageStatus;
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
}
