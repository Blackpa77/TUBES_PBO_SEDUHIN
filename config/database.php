<?php
// config/database.php

// Deteksi otomatis: Apakah sedang di Laptop (Localhost) atau Hosting?
$whitelist = ['127.0.0.1', '::1', 'localhost', 'tubes_pbo_seduhin.test'];

if (in_array($_SERVER['SERVER_NAME'], $whitelist)) {
    // =========================================
    // 1. SETTINGAN LOKAL (LARAGON / XAMPP)
    // =========================================
    $host = 'localhost';
    $db   = 'seduhin_db';  // Nama database di HeidiSQL kamu
    $user = 'root';        // Default Laragon
    $pass = '';            // Default Laragon (Kosong)

} else {
    // =========================================
    // 2. SETTINGAN HOSTING (INFINITYFREE)
    // =========================================
    // Data ini diambil dari screenshot akunmu sebelumnya
    $host = 'sql300.infinityfree.com';
    $db   = 'if0_40563141_seduhin';
    $user = 'if0_40563141';
    $pass = 'zP3VbW0qBchna'; // Password akun vPanel kamu
}

return [
    'host' => $host,
    'database' => $db,
    'username' => $user,
    'password' => $pass,
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];