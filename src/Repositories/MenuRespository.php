<?php
namespace App\Repositories;

use App\Core\Database;
use App\Models\Menu;
use PDO;

class MenuRepository
{
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function findById(int $id): ?Menu
    {
        $stmt = $this->db->prepare("SELECT * FROM menus WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $menu = new Menu($row);
        $menu->setId((int)$row['id']);
        if (!empty($row['created_at'])) $menu->createdAt = new \DateTime($row['created_at']);
        if (!empty($row['updated_at'])) $menu->updatedAt = new \DateTime($row['updated_at']);
        return $menu;
    }

    public function findAll(array $filters = []): array
    {
        $sql = "SELECT * FROM menus WHERE 1=1";
        $params = [];
        if (!empty($filters['category'])) { $sql .= " AND category = ?"; $params[] = $filters['category']; }
        $sql .= " ORDER BY created_at DESC";
        if (!empty($filters['limit'])) { $sql .= " LIMIT ?"; $params[] = (int)$filters['limit']; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = [];
        while ($r = $stmt->fetch()) {
            $m = new Menu($r); $m->setId((int)$r['id']);
            $res[] = $m;
        }
        return $res;
    }

    public function save(Menu $menu): bool { return $menu->save(); }
    public function delete(int $id): bool
    {
        $menu = $this->findById($id);
        if (!$menu) return false;
        return $menu->delete();
    }
}