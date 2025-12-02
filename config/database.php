<?php
// config/database.php

// Deteksi: Jika servernya localhost/laragon, pakai settingan lokal.
// Jika bukan (berarti di InfinityFree), pakai settingan hosting.
$whitelist = ['127.0.0.1', '::1', 'localhost', 'tubes_pbo_seduhin.test'];

if (in_array($_SERVER['SERVER_NAME'], $whitelist)) {
    // === MODE LOKAL (LARAGON) ===
    $host = 'localhost';
    $db   = 'seduhin_db';
    $user = 'root';
    $pass = '';
} else {
    // === MODE HOSTING (INFINITYFREE) ===
    // Masukkan data dari CPanel InfinityFree kamu
    $host = 'sql300.infinityfree.com'; // Cek Hostname di akunmu
    $db   = 'if0_40563141_seduhin';    // DB Name
    $user = 'if0_40563141';            // DB User
    $pass = 'zP3VbW0qBchna';           // Password VPanel (yang tadi)
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