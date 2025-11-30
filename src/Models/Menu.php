<?php
namespace App\Models;

use App\Core\Model;
use App\Traits\Validatable;

class Menu extends Model
{
    use Validatable;

    // Properti sesuai database
    private string $namaProduk;
    private int $idKategori;
    private float $harga;
    private string $deskripsi;
    private int $stok;
    private ?string $fotoProduk;

    // Constructor
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

    // --- GETTERS (Ini yang dicari error tadi) ---
    public function getStock(): int { return $this->stok; }
    public function getHarga(): float { return $this->harga; }
    public function getNamaProduk(): string { return $this->namaProduk; }
    public function getIdKategori(): int { return $this->idKategori; }
    public function getDeskripsi(): string { return $this->deskripsi; }
    public function getFotoProduk(): ?string { return $this->fotoProduk; }

    // --- LOGIC BISNIS (Penting untuk Transaksi) ---
    public function reduceStock(int $qty = 1): void 
    { 
        $this->stok -= $qty;
        if($this->stok < 0) $this->stok = 0; 
    }

    // --- VALIDASI ---
    public function validate(): bool
    {
        $this->clearErrors();
        $this->validateRequired('nama_produk', $this->namaProduk, 'Nama Produk');
        
        if ($this->harga <= 0) $this->addError('harga', 'Harga harus > 0');
        if ($this->stok < 0) $this->addError('stok', 'Stok tidak boleh minus');
        
        return !$this->hasErrors();
    }

    // --- MAPPING KE ARRAY (Untuk JSON) ---
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

    // --- KONFIGURASI MODEL ---
    protected static function tableName(): string { return 'produk'; }

    protected function insert(): bool
    {
        // Placeholder karena insert ditangani Repository
        return false; 
    }

    protected function update(): bool
    {
        // Placeholder karena update ditangani Repository
        return false;
    }

    public function delete(): bool
    {
        // Placeholder karena delete ditangani Repository
        return false;
    }
}