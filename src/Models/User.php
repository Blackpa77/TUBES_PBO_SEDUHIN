<?php
namespace App\Models;

use App\Traits\Timestampable;
use App\Interfaces\Authenticatable;

abstract class User implements Authenticatable
{
    use Timestampable;

    protected ?int $id;
    protected string $name;
    protected string $email;
    protected string $password;

    public function __construct(?int $id, string $name, string $email, string $password)
    {
        $this->id       = $id;
        $this->name     = $name;
        $this->email    = $email;
        $this->password = $password;
        $this->touch();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'email'     => $this->email,
            'created_at'=> $this->createdAt,
            'updated_at'=> $this->updatedAt,
        ];
    }
}
