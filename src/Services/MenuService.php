<?php
namespace App\Services;

use App\Repositories\MenuRepository;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

class MenuService
{
    private MenuRepository $repo;
    public function __construct(MenuRepository $r) { $this->repo = $r; }

    public function list(array $filters = []): array {
        // PERBAIKAN: Ubah all() menjadi findAll()
        $items = $this->repo->findAll($filters);
        return array_map(fn($m)=>$m->toArray(), $items);
    }

    public function get(int $id): array {
        // PERBAIKAN: Ubah find() menjadi findById() sesuai Repository
        $m = $this->repo->findById($id);
        if (!$m) throw new NotFoundException("Menu not found");
        return $m->toArray();
    }

    private function validate(array $data, bool $forUpdate=false): void {
        $errors = [];
        if (!$forUpdate || isset($data['name'])) {
            // Sesuaikan key dengan input JSON (biasanya camelCase atau snake_case dari frontend)
            // Kita cek 'nama_produk' (DB) atau 'name' (JSON request)
            $name = $data['nama_produk'] ?? $data['name'] ?? '';
            if (empty($name)) $errors['name'] = 'Name is required';
        }
        if (!$forUpdate || isset($data['price'])) {
            $price = $data['harga'] ?? $data['price'] ?? 0;
            if (!is_numeric($price)) $errors['price'] = 'Price must be numeric';
        }
        if ($errors) throw new ValidationException("Validation Error", $errors);
    }

    public function create(array $data): array {
        // Mapping input JSON (name) ke Database (nama_produk) agar Model paham
        if(isset($data['name'])) $data['nama_produk'] = $data['name'];
        if(isset($data['price'])) $data['harga'] = $data['price'];
        if(isset($data['category'])) $data['id_kategori'] = $data['category']; // Asumsi input category adalah ID

        $this->validate($data);
        
        // Buat object Menu baru
        $menu = new \App\Models\Menu($data);
        if(!$menu->validate()) throw new ValidationException("Model validation failed", $menu->getErrors());
        
        $this->repo->save($menu);
        return $menu->toArray();
    }

    public function update(int $id, array $data): array {
        $existing = $this->repo->findById($id);
        if (!$existing) throw new NotFoundException("Menu not found");
        
        // Mapping update
        if(isset($data['name'])) $data['nama_produk'] = $data['name'];
        if(isset($data['price'])) $data['harga'] = $data['price'];
        
        // Gabungkan data lama dengan baru (manual merge sederhana)
        $newData = array_merge($existing->toArray(), $data);
        $menu = new \App\Models\Menu($newData);
        $menu->setId($id);
        
        if(!$menu->validate()) throw new ValidationException("Validation failed", $menu->getErrors());
        
        $this->repo->save($menu);
        return $menu->toArray();
    }

    public function delete(int $id): void {
        $deleted = $this->repo->delete($id);
        if (!$deleted) throw new NotFoundException("Menu not found");
    }
}