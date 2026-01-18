<?php

namespace App\Exceptions;

use Exception;

class ImageTooLargeException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            message: __('images.too_large'),
            status: 413,
            errorCode: 'IMAGE_TOO_LARGE'
        );
    }
}
