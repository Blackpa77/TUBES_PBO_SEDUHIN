<?php
namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected array $errors = [];

    public function __construct(string $message = "Validation failed", array $errors = [], int $code = 400)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    /**
     * Mengambil daftar error
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
