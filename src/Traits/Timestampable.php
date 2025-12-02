<?php
namespace App\Traits;

use DateTime;

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

    // --- INI YANG KURANG TADI ---
    public function setCreatedAt(DateTime $date): void 
    { 
        $this->createdAt = $date; 
    }

    public function setUpdatedAt(DateTime $date): void 
    { 
        $this->updatedAt = $date; 
    }
    // ----------------------------

    public function getCreatedAt(): ?string 
    { 
        return $this->createdAt?->format('Y-m-d H:i:s'); 
    }

    public function getUpdatedAt(): ?string 
    { 
        return $this->updatedAt?->format('Y-m-d H:i:s'); 
    }
}