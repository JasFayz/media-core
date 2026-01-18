<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected int $status;
    protected ?string $errorCode;

    public function __construct(
        string  $message,
        int     $status = 400,
        ?string $errorCode = null
    )
    {
        parent::__construct($message);
        $this->status = $status;
        $this->errorCode = $errorCode;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function errorCode(): ?string
    {
        return $this->errorCode;
    }
}
