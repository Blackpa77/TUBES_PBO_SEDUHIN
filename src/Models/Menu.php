<?php
namespace App\Models;

use App\Core\Model;
use App\Traits\Validatable;

class Menu extends Model
{
    use Validatable;

    // Mapping properti sesuai kolom database 'produk'
    private string $namaProduk;
    private int $idKategori;
    private float $harga;
    private string $deskripsi;
    private int $stok;
    private ?string $fotoProduk;

    public function __construct(array $data = [])
    {
        if ($data) $this->fill($data);
    }

    private function fill(array $d): void
    {
        // Mapping dari snake_case (DB) ke camelCase (PHP)
        $this->namaProduk = $d['nama_produk'] ?? '';
        $this->idKategori = (int)($d['id_kategori'] ?? 0);
        $this->harga = (float)($d['harga'] ?? 0.0);
        $this->deskripsi = $d['deskripsi'] ?? '';
        $this->stok = (int)($d['stok'] ?? 0);
        $this->fotoProduk = $d['foto_produk'] ?? null;
    }

    public function validate(): bool
    {
        $this->clearErrors();
        $this->validateRequired('nama_produk', $this->namaProduk, 'Nama Produk');
        if ($this->harga <= 0) $this->addError('harga', 'Harga harus > 0');
        if ($this->stok < 0) $this->addError('stok', 'Stok tidak boleh minus');
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
        ];
    }

    // PENTING: Arahkan ke tabel yang benar
    protected static function tableName(): string { return 'produk'; }

    protected function insert(): bool
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        $sql = "INSERT INTO produk (nama_produk, id_kategori, harga, deskripsi, stok, foto_produk, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([
            $this->namaProduk, $this->idKategori, $this->harga, 
            $this->deskripsi, $this->stok, $this->fotoProduk, 
            $this->createdAt->format('Y-m-d H:i:s')
        ]);
        if ($res) $this->id = (int)$db->lastInsertId();
        return $res;
    }

    protected function update(): bool
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        $sql = "UPDATE produk SET nama_produk=?, id_kategori=?, harga=?, deskripsi=?, stok=?, foto_produk=?, updated_at=? WHERE id=?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->namaProduk, $this->idKategori, $this->harga, 
            $this->deskripsi, $this->stok, $this->fotoProduk, 
            $this->updatedAt->format('Y-m-d H:i:s'), $this->id
        ]);
    }

    public function delete(): bool
    {
        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM produk WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    // Getters & Setters untuk Logic
    public function getPrice(): float { return $this->harga; }
    public function getStock(): int { return $this->stok; }
    public function reduceStock(int $qty = 1): void { $this->stok -= $qty; }
    // Setter opsional jika dibutuhkan Controller
    public function setIdKategori(int $id) { $this->idKategori = $id; }
}