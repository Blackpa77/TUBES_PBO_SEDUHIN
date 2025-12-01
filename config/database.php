<?php
// config/database.php
return [
    'host' => 'localhost', // Di hosting biasanya tetap localhost
    'database' => 'id210000_seduhin_db', // Nanti kita ganti sesuai nama DB hosting
    'username' => 'id210000_user',       // Nanti kita ganti sesuai user hosting
    'password' => 'PasswordKamu123!',    // Nanti kita ganti
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];