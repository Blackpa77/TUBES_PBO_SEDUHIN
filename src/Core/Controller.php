<?php
namespace App\Core;

use App\Builders\ApiResponseBuilder;

/**
 * Abstract Controller - Base untuk semua controllers
 */
abstract class Controller
{
    protected function getJson(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    protected function send($data, int $status = 200): void
    {
        ApiResponseBuilder::from($data, $status)->send();
    }
}
