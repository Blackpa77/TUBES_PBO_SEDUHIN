<?php
namespace App\Repositories;

use App\Models\Menu;

interface MenuRepositoryInterface
{
    // UBAH DARI find() JADI findById()
    public function findById(int $id): ?Menu;
    
    public function findAll(array $filters = []): array;
    public function save(Menu $menu): bool;
    public function delete(int $id): bool;
}