<?php
// ValidationException.php
namespace App\Exceptions;
class ValidationException extends \Exception {
    private array $errors;
    public function __construct(array $errors, $message='Validation failed', $code=422) {
        parent::__construct($message,$code);
        $this->errors = $errors;
    }
    public function getErrors(): array { return $this->errors; }
}