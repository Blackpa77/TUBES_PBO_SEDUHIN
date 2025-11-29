<?php
namespace App\Services;

use App\Repositories\MenuRepository;
use App\Exceptions\ValidationException;

class MenuService
{
    private MenuRepository $repo;
    public function __construct(MenuRepository $r) { $this->repo = $r; }

    public function list(array $filters = []): array {
        $limit = isset($filters['limit']) ? (int)$filters['limit'] : 50;
        $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;
        $items = $this->repo->all($filters, $limit, $offset);
        return array_map(fn($m)=>$m->toArray(), $items);
    }

    public function get(int $id): array {
        $m = $this->repo->find($id);
        return $m->toArray();
    }

    private function validate(array $data, bool $forUpdate=false): void {
        $errors = [];
        if (!$forUpdate || isset($data['name'])) {
            if (empty($data['name'])) $errors['name'] = 'Name is required';
        }
        if (!$forUpdate || isset($data['price'])) {
            if (!isset($data['price']) || !is_numeric($data['price'])) $errors['price'] = 'Price must be numeric';
        }
        if ($errors) throw new ValidationException($errors);
    }

    public function create(array $data): array {
        $this->validate($data);
        $menu = $this->repo->create($data);
        return $menu->toArray();
    }

    public function update(int $id, array $data): array {
        $this->validate($data, true);
        $menu = $this->repo->update($id, $data);
        return $menu->toArray();
    }

    public function delete(int $id): void {
        $this->repo->delete($id);
    }
}
