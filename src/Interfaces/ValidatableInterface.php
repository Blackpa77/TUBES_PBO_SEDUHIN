<?php
namespace App\Interfaces;

interface ValidatableInterface {
    public function validate(): bool;
    public function getErrors(): array;
}
