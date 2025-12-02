<?php
// Deteksi otomatis: Apakah sedang di Laptop atau Hosting?
$whitelist = ['127.0.0.1', '::1', 'localhost', 'tubes_pbo_seduhin.test'];

if (in_array($_SERVER['SERVER_NAME'], $whitelist)) {
    // === SETTINGAN LOKAL (LARAGON) ===
    $host = 'localhost';
    $db   = 'seduhin_db';
    $user = 'root';
    $pass = ''; 
} else {
    // === SETTINGAN HOSTING (INFINITYFREE) ===
    // Data diambil dari screenshot akunmu
    $host = 'sql300.infinityfree.com';
    $db   = 'if0_40563141_seduhin';
    $user = 'if0_40563141';
    $pass = 'zP3VbW0qBchna';
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