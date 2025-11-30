<?php
namespace App\Models;

use App\Traits\Validatable;
use App\Traits\Timestampable;
use DateTime;

class Menu
{
    use Validatable, Timestampable;

    private ?int $id = null;
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

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getNamaProduk(): string { return $this->namaProduk; }
    public function getIdKategori(): int { return $this->idKategori; }
    public function getHarga(): float { return $this->harga; }
    public function getDeskripsi(): string { return $this->deskripsi; }
    public function getStok(): int { return $this->stok; }
    public function getFotoProduk(): ?string { return $this->fotoProduk; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    
    // Logic Bisnis (Validasi & Stok)
    public function validate(): bool
    {
        $this->clearErrors();
        $this->validateRequired('nama_produk', $this->namaProduk, 'Nama Produk');
        if ($this->harga <= 0) $this->addError('harga', 'Harga harus lebih dari 0');
        if ($this->stok < 0) $this->addError('stok', 'Stok tidak boleh minus');
        return !$this->hasErrors();
    }

    public function reduceStock(int $qty = 1): void 
    { 
        $this->stok -= $qty;
        if($this->stok < 0) $this->stok = 0; 
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
}