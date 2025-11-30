<?php
namespace App\Repositories;

use App\Models\Menu;

interface MenuRepositoryInterface
{
    public function find(int $id): ?Menu;
    public function findAll(array $filters = []): array; // <--- INI WAJIB ADA
    public function save(Menu $menu): bool;
    public function delete(int $id): bool;
}