<?php
namespace App\Services;

use App\Repositories\MenuRepository;
use App\Models\Menu;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;

class MenuService
{
    private MenuRepository $repo;
    public function __construct(MenuRepository $r) { $this->repo = $r; }

    public function list(array $filters = []): array { return array_map(fn($m)=>$m->toArray(), $this->repo->findAll($filters)); }

    public function get(int $id): array
    {
        $m = $this->repo->findById($id);
        if (!$m) throw new NotFoundException("Menu not found");
        return $m->toArray();
    }

    public function create(array $data): array
    {
        $menu = new Menu($data);
        if (!$menu->validate()) throw new ValidationException("Validation failed", $menu->getErrors());
        $this->repo->save($menu);
        return $menu->toArray();
    }

    public function update(int $id, array $data): array
    {
        $existing = $this->repo->findById($id);
        if (!$existing) throw new NotFoundException("Menu not found");
        // merge fields
        $merged = array_merge($existing->toArray(), $data);
        $menu = new Menu($merged);
        $menu->setId($id);
        if (!$menu->validate()) throw new ValidationException("Validation failed", $menu->getErrors());
        $this->repo->save($menu);
        return $menu->toArray();
    }

    public function delete(int $id): bool
    {
        $deleted = $this->repo->delete($id);
        if (!$deleted) throw new NotFoundException("Menu not found");
        return true;
    }
}