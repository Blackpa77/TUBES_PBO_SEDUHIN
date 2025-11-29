<?php
namespace App\Factories;

use App\Models\Menu;

class MenuFactory
{
    public static function make(array $data): Menu {
        $allowed = ['id','name','price','category','description','created_at','updated_at','is_active'];
        $filtered = array_filter($data, fn($k)=>in_array($k,$allowed), ARRAY_FILTER_USE_KEY);
        return new Menu($filtered);
    }
}
