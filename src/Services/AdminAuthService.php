<?php
namespace App\Services;

use App\Repositories\AdminRepository;
use App\Exceptions\ValidationException;

class AdminAuthService
{
    private AdminRepository $repo;

    public function __construct(AdminRepository $repo)
    {
        $this->repo = $repo;
    }

    public function login(string $email, string $password): array
    {
        $admin = $this->repo->findByEmail($email);

        // Validasi sederhana (Plain text password sesuai database kamu)
        if (!$admin || $admin->password !== $password) {
            throw new ValidationException("Email atau Password salah!");
        }

        // Return data sukses
        return [
            'id' => $admin->id,
            'nama' => $admin->nama,
            'email' => $admin->email,
            'token' => base64_encode("TOKEN_" . $admin->id . "_" . time())
        ];
    }
}