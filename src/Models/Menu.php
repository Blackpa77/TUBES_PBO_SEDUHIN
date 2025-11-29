<?php
namespace App\Models;

use App\Core\Model;
use App\Traits\Validatable;

class Menu extends Model
{
    use Validatable;

    private string $name;
    private string $category;
    private float $price;
    private int $stock;

    public function __construct(array $data = [])
    {
        if ($data) $this->fill($data);
    }

    private function fill(array $d): void
    {
        $this->name = $d['name'] ?? '';
        $this->category = $d['category'] ?? 'general';
        $this->price = (float)($d['price'] ?? 0.0);
        $this->stock = (int)($d['stock'] ?? 0);
    }

    public function validate(): bool
    {
        $this->clearErrors();
        $this->validateRequired('name', $this->name, 'Name');
        if ($this->price <= 0) $this->addError('price', 'Price must be > 0');
        return !$this->hasErrors();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name'=> $this->name,
            'category'=> $this->category,
            'price'=> $this->price,
            'stock'=> $this->stock,
            'created_at'=> $this->getCreatedAt(),
            'updated_at'=> $this->getUpdatedAt(),
        ];
    }

    protected static function tableName(): string { return 'menus'; }

    protected function insert(): bool
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        $sql = "INSERT INTO menus (name, category, price, stock, created_at) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$this->name, $this->category, $this->price, $this->stock, $this->createdAt->format('Y-m-d H:i:s')]);
        if ($res) $this->id = (int)$db->lastInsertId();
        return $res;
    }

    protected function update(): bool
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        $sql = "UPDATE menus SET name=?, category=?, price=?, stock=?, updated_at=? WHERE id=?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$this->name, $this->category, $this->price, $this->stock, $this->updatedAt->format('Y-m-d H:i:s'), $this->id]);
    }

    public function delete(): bool
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM menus WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    // getters
    public function getPrice(): float { return $this->price; }
    public function getStock(): int { return $this->stock; }
    public function reduceStock(int $qty = 1): void { $this->stock -= $qty; if ($this->stock < 0) $this->stock = 0; }
    public function addStock(int $n): void { $this->stock += $n; }
}
