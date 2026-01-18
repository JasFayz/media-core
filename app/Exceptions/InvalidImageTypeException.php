<?php

namespace App\Exceptions;

class InvalidImageTypeException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            message: __('images.invalid_image_type'),
            status: 413,
            errorCode: 'IMAGE_INVALID_TYPE'
        );
    }
}
