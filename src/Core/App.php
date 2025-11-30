<?php
namespace App\Core;

class App
{
    protected static array $bindings = [];

    public static function bind(string $abstract, $concrete): void
    {
        static::$bindings[$abstract] = $concrete;
    }

    public static function get(string $abstract)
    {
        if (!isset(static::$bindings[$abstract])) {
            throw new \Exception("No binding for {$abstract}");
        }
        $concrete = static::$bindings[$abstract];
        return is_callable($concrete) ? $concrete() : $concrete;
    }

    public static function resolve(string $class)
    {
        $ref = new \ReflectionClass($class);
        $ctor = $ref->getConstructor();
        if (!$ctor) return new $class();
        
        $params = $ctor->getParameters();
        $args = [];
        
        foreach ($params as $p) {
            $type = $p->getType();
            if ($type && !$type->isBuiltin()) {
                // Rekursif: Cari dependency di container
                $args[] = static::get($type->getName());
            } else {
                $args[] = null;
            }
        }
        return $ref->newInstanceArgs($args);
    }
}