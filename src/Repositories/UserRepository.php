<?php
namespace App\Repositories;

use App\Core\Database;

class UserRepository
{
    protected \PDO $db;
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM customers WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO customers (name, email, phone, password) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['name'], $data['email'], $data['phone'] ?? null, $data['password']]);
        return (int)$this->db->lastInsertId();
    }
}
