<?php
namespace App\Interfaces;
interface Authenticatable {
    public function getAuthIdentifier(): mixed;
    public function getAuthPassword(): string;
}