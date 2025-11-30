<?php
namespace App\Models;

use App\Core\Model; // Pastikan extend Model
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;

class Order extends Model
{
    // Properti publik agar mudah diakses Repository
    public ?int $id = null;
    public string $namaPelanggan = 'Guest'; // Sesuai DB
    public float $total = 0;
    public string $status = 'pending'; // Simpan string saja biar gampang masuk DB
    public array $items = [];

    public function __construct(array $data = [])
    {
        // Mapping dari payload JSON ke properti
        $this->namaPelanggan = $data['customer_name'] ?? $data['nama_pelanggan'] ?? 'Guest';
        
        // Handle Status (jika dikirim sebagai Enum atau String)
        if (isset($data['status'])) {
            $this->status = $data['status'] instanceof OrderStatus 
                ? $data['status']->value 
                : (string)$data['status'];
        }

        $this->items = $data['items'] ?? [];
        $this->total = $data['total'] ?? 0;
    }

    public function validate(): bool
    {
        // Validasi sederhana
        if (empty($this->items)) return false;
        return true;
    }

    // Wajib ada karena extend Model
    protected static function tableName(): string { return 'orders'; }
    public function toArray(): array {
        return [
            'id' => $this->id,
            'nama_pelanggan' => $this->namaPelanggan,
            'total' => $this->total,
            'status' => $this->status,
            'items' => $this->items,
            'created_at' => $this->getCreatedAt()
        ];
    }
    
    // Placeholder untuk satisfy abstract class
    protected function insert(): bool { return false; } 
    protected function update(): bool { return false; }
    public function delete(): bool { return false; }
}