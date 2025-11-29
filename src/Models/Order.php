<?php
namespace App\Models;

use App\Core\Model;
use App\Traits\Validatable;

class Order extends Model
{
    use Validatable;

    private int $customerId;
    private string $status = 'pending';
    private float $total = 0.0;

    public array $items = []; // array of OrderItem

    public function __construct(array $data = [])
    {
        if ($data) {
            $this->customerId = $data['customer_id'] ?? 0;
            $this->status = $data['status'] ?? 'pending';
            $this->total = $data['total'] ?? 0.0;
            $this->items = $data['items'] ?? [];
        }
    }

    public function validate(): bool
    {
        $this->clearErrors();
        if ($this->customerId <= 0) $this->addError('customer_id','Customer required');
        if (empty($this->items)) $this->addError('items','Order must have items');
        return !$this->hasErrors();
    }

    public function toArray(): array
    {
        return [
            'id'=>$this->id,
            'customer_id'=>$this->customerId,
            'status'=>$this->status,
            'total'=>$this->total,
            'items'=>array_map(fn($i)=>$i->toArray(), $this->items),
            'created_at'=>$this->getCreatedAt()
        ];
    }

    protected static function tableName(): string { return 'orders'; }

    protected function insert(): bool
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        $sql = "INSERT INTO orders (customer_id, status, total, created_at) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$this->customerId, $this->status, $this->total, $this->createdAt->format('Y-m-d H:i:s')]);
        if ($res) $this->id = (int)$db->lastInsertId();
        return $res;
    }
    protected function update(): bool
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        $sql = "UPDATE orders SET status=?, total=?, updated_at=? WHERE id=?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$this->status, $this->total, $this->updatedAt->format('Y-m-d H:i:s'), $this->id]);
    }
    public function delete(): bool { /* implement if needed */ return false; }
}
