<?php
namespace App\Core;

use App\Traits\Timestampable;

/**
 * Abstract Model - Base class untuk semua model
 * Menerapkan Abstraksi dan Template Method Pattern
 * Menerapkan DRY principle
 */
abstract class Model
{
    use Timestampable;

    protected ?int $id = null;

    public function getId(): ?int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    abstract public function validate(): bool;
    abstract public function toArray(): array;
    abstract protected static function tableName(): string;

    public function save(): bool
    {
        if (!$this->validate()) return false;
        $this->updateTimestamps();
        if ($this->id === null) return $this->insert();
        return $this->update();
    }

    abstract protected function insert(): bool;
    abstract protected function update(): bool;
    abstract public function delete(): bool;
}
