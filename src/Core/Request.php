<?php
// Request.php
namespace App\Core;
class Request {
    public static function all(): array {
        return array_merge($_GET ?? [], $_POST ?? []);
    }
}