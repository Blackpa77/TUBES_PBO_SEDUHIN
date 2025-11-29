<?php
namespace App\Services;

use App\Repositories\UserRepository;

class AuthService
{
    protected UserRepository $repo;

    public function __construct(UserRepository $repo)
    {
        $this->repo = $repo;
    }

    public function register(array $payload): array
    {
        if (empty($payload['email']) || empty($payload['password']) || empty($payload['name'])) {
            throw new \InvalidArgumentException('Missing fields', 400);
        }
        $existing = $this->repo->findByEmail($payload['email']);
        if ($existing) throw new \Exception('Email already used', 409);
        $payload['password'] = password_hash($payload['password'], PASSWORD_DEFAULT);
        $id = $this->repo->create($payload);
        return ['id' => $id];
    }

    public function login(string $email, string $password): array
    {
        $user = $this->repo->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            throw new \Exception('Invalid credentials', 401);
        }
        // simple token stub
        $token = base64_encode($user['id'] . '|' . time());
        return ['token' => $token, 'user' => $user];
    }
}
