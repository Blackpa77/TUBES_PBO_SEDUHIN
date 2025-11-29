<?php
namespace App\Services;

/**
 * Placeholder LoanService to satisfy project structure
 * Minimal implementation — extend as needed
 */
class LoanService
{
    public function calculateDue(float $amount, int $days): float
    {
        $rate = 0.01; // dummy daily rate
        return $amount * (1 + $rate * $days);
    }
}
