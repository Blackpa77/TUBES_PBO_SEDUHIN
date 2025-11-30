<?php

namespace App\Models;

use App\Core\Model; // Asumsi parent class dasar
use App\Traits\Validatable; // Asumsi trait validasi Anda

class Menu extends Model
{
    use Validatable;

    private ?int $id = null;
    private string $namaProduk;
    private float $harga;
    private int $stok;

    // Constructor untuk inisialisasi cepat (opsional tapi membantu)
    public function __construct(string $nama = '', float $harga = 0, int $stok = 0)
    {
        $this->namaProduk = $nama;
        $this->harga = $harga;
        $this->stok = $stok;
    }

    // Getters & Setters
    public function getId(): ?int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getNamaProduk(): string { return $this->namaProduk; }
    public function getHarga(): float { return $this->harga; }
    public function getStok(): int { return $this->stok; }

    // Logika Validasi TETAP di sini (karena validasi adalah aturan data)
    public function validate(): bool
    {
        $this->clearErrors();
        
        $this->validateRequired('nama_produk', $this->namaProduk, 'Nama Produk');
        
        if ($this->harga <= 0) {
            $this->addError('harga', 'Harga harus lebih dari 0');
        }
        
        if ($this->stok < 0) {
            $this->addError('stok', 'Stok tidak boleh minus');
        }

        return !$this->hasErrors();
    }
}