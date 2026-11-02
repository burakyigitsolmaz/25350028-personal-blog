<?php
/**
 * Örnek yapılandırma dosyası.
 * Bu dosyayı "config.local.php" olarak kopyalayın ve değerleri
 * kendi ortamınıza göre doldurun. config.local.php .gitignore'dadır.
 */
return [
    // --- Veritabanı ---
    'db_host'    => '127.0.0.1',
    'db_name'    => 'kisisel_blog_db',
    'db_user'    => 'root',
    'db_pass'    => '',

    // --- Site ---
    'base_url'   => '/kisisel-blog',        // htdocs altındaki klasör adı
    'salt'       => 'LUTFEN-UZUN-RASGELE-BIR-STRING-YAZIN',
];
