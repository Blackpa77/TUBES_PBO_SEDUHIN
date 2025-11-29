<?php
namespace App\Traits;

use DateTime;

/**
 * Trait untuk menambahkan timestamp otomatis
 * Traits memungkinkan reuse kode tanpa inheritance
 */
trait Timestampable
{
    protected ?DateTime $createdAt = null;
    protected ?DateTime $updatedAt = null;

    protected function updateTimestamps(): void
    {
        $now = new DateTime();
        if ($this->createdAt === null) $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getCreatedAt(): ?string { return $this->createdAt?->format('Y-m-d H:i:s'); }
    public function getUpdatedAt(): ?string { return $this->updatedAt?->format('Y-m-d H:i:s'); }
}
