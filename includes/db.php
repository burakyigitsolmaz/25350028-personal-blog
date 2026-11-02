<?php
/**
 * PDO bağlantısı (tek ton).
 * Tüm sorgular prepared statement ile yürütülür; emülasyon kapalı.
 */

require_once dirname(__DIR__) . '/config/config.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    blog_config_boot();

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_NAME
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        exit('Veritabanı bağlantısına erişilemiyor. XAMPP &gt; MySQL çalışıyor mu? (Detay: '
            . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . ')');
    }

    return $pdo;
}
