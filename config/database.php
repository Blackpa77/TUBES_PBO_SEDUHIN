<?php
// config/database.php

// Default: Localhost (Laragon/XAMPP)
$host = 'localhost';
$db   = 'seduhin_db';
$user = 'root';
$pass = '';
$port = 3306;

// Cek apakah ada Environment Variable (Dari Railway/Render)
if (getenv('MYSQLHOST')) {
    $host = getenv('MYSQLHOST');
    $db   = getenv('MYSQLDATABASE');
    $user = getenv('MYSQLUSER');
    $pass = getenv('MYSQLPASSWORD');
    $port = getenv('MYSQLPORT');
}

return [
    'host' => $host,
    'database' => $db,
    'username' => $user,
    'password' => $pass,
    'port' => $port,
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];