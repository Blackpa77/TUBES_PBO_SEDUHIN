<?php
namespace App\Models;

use App\Core\Model;
use App\Traits\Validatable;

class Menu extends Model
{
    use Validatable;

    private string $namaProduk;
    private int $idKategori;
    private float $harga;
    private string $deskripsi;
    private int $stok;
    private ?string $fotoProduk;

    public function __construct(
        string $namaProduk = '', 
        float $harga = 0, 
        int $stok = 0, 
        int $idKategori = 1,
        string $deskripsi = '',
        ?string $fotoProduk = null
    ) {
        $this->namaProduk = $namaProduk;
        $this->harga = $harga;
        $this->stok = $stok;
        $this->idKategori = $idKategori;
        $this->deskripsi = $deskripsi;
        $this->fotoProduk = $fotoProduk;
    }

    // --- GETTERS (DUA BAHASA) ---
    public function getId(): ?int { return $this->id; }
    
    // Versi Indo (Sesuai DB)
    public function getNamaProduk(): string { return $this->namaProduk; }
    public function getHarga(): float { return $this->harga; }
    public function getStok(): int { return $this->stok; }
    public function getIdKategori(): int { return $this->idKategori; }
    public function getDeskripsi(): string { return $this->deskripsi; }
    public function getFotoProduk(): ?string { return $this->fotoProduk; }

    // Versi Inggris (Agar Service tidak error)
    public function getName(): string { return $this->namaProduk; }
    public function getPrice(): float { return $this->harga; }
    public function getStock(): int { return $this->stok; }

    public function reduceStock(int $qty = 1): void 
    { 
        $this->stok -= $qty;
        if($this->stok < 0) $this->stok = 0; 
    }

    public function validate(): bool
    {
        $this->clearErrors();
        $this->validateRequired('nama_produk', $this->namaProduk, 'Nama Produk');
        if ($this->harga <= 0) $this->addError('harga', 'Harga harus > 0');
        return !$this->hasErrors();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nama_produk' => $this->namaProduk,
            'id_kategori' => $this->idKategori,
            'harga' => $this->harga,
            'deskripsi' => $this->deskripsi,
            'stok' => $this->stok,
            'foto_produk' => $this->fotoProduk,
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt(),
        ];
    }

    protected static function tableName(): string { return 'produk'; }
    protected function insert(): bool { return false; }
    protected function update(): bool { return false; }
    public function delete(): bool { return false; }
}