<?php
namespace App\Traits;

trait Validatable
{
    protected array $errors = [];

    public function getErrors(): array { return $this->errors; }
    public function hasErrors(): bool { return !empty($this->errors); }
    protected function addError(string $field, string $msg): void { $this->errors[$field][] = $msg; }
    protected function clearErrors(): void { $this->errors = []; }

    protected function validateRequired(string $field, $value, string $label): void
    {
        if ($value === null || $value === '') $this->addError($field, "{$label} is required");
    }
}
