<?php
namespace App\Repositories;

use App\Core\Database;
use App\Factories\MenuFactory;
use App\Models\Menu;
use App\Exceptions\NotFoundException;

class MenuRepository
{
    private \PDO $db;
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function all(array $filters = [], int $limit = 100, int $offset = 0): array {
        $sql = "SELECT * FROM menus WHERE 1=1";
        $params = [];
        if (!empty($filters['q'])) {
            $sql .= " AND name LIKE :q";
            $params[':q'] = "%{$filters['q']}%";
        }
        if (isset($filters['category'])) {
            $sql .= " AND category = :category";
            $params[':category'] = $filters['category'];
        }
        $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_map(fn($r)=>MenuFactory::make($r), $rows);
    }

    public function find(int $id): Menu {
        $stmt = $this->db->prepare("SELECT * FROM menus WHERE id = :id");
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch();
        if (!$row) throw new NotFoundException("Menu not found");
        return MenuFactory::make($row);
    }

    public function create(array $data): Menu {
        $sql = "INSERT INTO menus (name, price, category, description, is_active, created_at, updated_at)
                VALUES (:name,:price,:category,:description,:is_active,:created_at,:updated_at)";
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name'=>$data['name'],
            ':price'=>$data['price'],
            ':category'=>$data['category'] ?? 'coffee',
            ':description'=>$data['description'] ?? null,
            ':is_active'=>isset($data['is_active']) ? (int)$data['is_active'] : 1,
            ':created_at'=>$now,
            ':updated_at'=>$now
        ]);
        $id = (int)$this->db->lastInsertId();
        return $this->find($id);
    }

    public function update(int $id, array $data): Menu {
        $menu = $this->find($id);
        $fields = [];
        $params = [];
        foreach (['name','price','category','description','is_active'] as $f) {
            if (array_key_exists($f,$data)) {
                $fields[] = "$f = :$f";
                $params[":$f"] = $data[$f];
            }
        }
        if (empty($fields)) return $menu;
        $params[':updated_at'] = date('Y-m-d H:i:s');
        $sql = "UPDATE menus SET " . implode(',',$fields) . ", updated_at = :updated_at WHERE id = :id";
        $params[':id'] = $id;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $this->find($id);
    }

    public function delete(int $id): bool {
        $this->find($id); // throws if not exists
        $stmt = $this->db->prepare("DELETE FROM menus WHERE id = :id");
        return $stmt->execute([':id'=>$id]);
    }
}
