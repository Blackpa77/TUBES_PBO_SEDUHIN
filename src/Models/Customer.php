<?php
namespace App\Models;

class Customer
{
    public ?int $id = null;
    public string $name = '';
    public ?string $phone = null;
    public ?string $email = null;
    public ?string $password = null;
}
