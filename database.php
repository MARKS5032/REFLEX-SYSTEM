<?php

define('DB_HOST', 'sql103.byetcluster.com');
define('DB_NAME', 'if0_42770188_reflex');
define('DB_USER', 'if0_42770188');
define('DB_PASS', 'fa2000th');
define('DB_CHARSET', 'utf8mb4');

function getDbConnection(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log the real error server-side only.
        error_log('Reflex DB connection failed: ' . $e->getMessage());

        // Show a generic, safe message to the user.
        http_response_code(500);
        if (session_status() === PHP_SESSION_NONE) {
            // no-op, header/footer may not be loaded yet
        }
        die('<!DOCTYPE html><html><head><title>Reflex - Service Unavailable</title>
            <link rel="stylesheet" href="/reflex/assets/css/style.css"></head>
            <body><div class="empty-state" style="margin:80px auto;max-width:480px;text-align:center;">
            <h2>Something went wrong</h2>
            <p>Reflex could not connect to the database right now. Please try again shortly, or contact the administrator.</p>
            </div></body></html>');
    }
}
