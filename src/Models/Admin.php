<?php
namespace App\Models;

class Admin
{
    public ?int $id;
    public string $email;
    public string $password;
    public string $nama;

    public function __construct(array $data)
    {
        $this->id = $data['id_admin'] ?? null;
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->nama = $data['nama_admin'] ?? '';
    }
}