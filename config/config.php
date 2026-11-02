<?php
/**
 * Yapılandırma yükleyici.
 * 1) config.local.php varsa onu kullanır (gerçek bilgiler),
 * 2) yoksa config.example.php kullanır (güvenli varsayılanlar).
 */

function blog_config_load(): array
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }

    $localPath    = __DIR__ . '/config.local.php';
    $passwordPath = __DIR__ . '/config.example.php';

    $cfg = file_exists($localPath)
        ? require $localPath
        : require $passwordPath;

    if (!is_array($cfg)) {
        http_response_code(500);
        exit('Yapılandırma dosyası geçersiz.');
    }

    // Zorunlu anahtarlar
    foreach (['db_host', 'db_name', 'db_user', 'base_url', 'salt'] as $key) {
        if (!array_key_exists($key, $cfg)) {
            http_response_code(500);
            exit('Yapılandırmada eksik anahtar: ' . $key);
        }
    }

    return $cfg;
}

/** Sabitleri tanımla (tek seferlik). */
function blog_config_boot(): void
{
    $cfg = blog_config_load();

    defined('DB_HOST')   || define('DB_HOST', $cfg['db_host']);
    defined('DB_NAME')   || define('DB_NAME', $cfg['db_name']);
    defined('DB_USER')   || define('DB_USER', $cfg['db_user']);
    defined('DB_PASS')   || define('DB_PASS', $cfg['db_pass'] ?? '');
    defined('BASE_URL')  || define('BASE_URL', rtrim($cfg['base_url'], '/'));
    defined('APP_SALT')  || define('APP_SALT', $cfg['salt']);
    defined('ROOT_PATH') || define('ROOT_PATH', dirname(__DIR__));
}
