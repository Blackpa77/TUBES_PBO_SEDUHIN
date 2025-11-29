<?php
namespace App\Interfaces;

interface Payable
{
    public function pay(array $data): array;
}
