<?php

namespace App\Repositories;

use App\Models\Menu;

interface MenuRepositoryInterface
{
    public function find(int $id): ?Menu;
    public function save(Menu $menu): bool; // Menangani Insert & Update
    public function delete(int $id): bool;
}