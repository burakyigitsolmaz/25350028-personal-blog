# Kişisel Blog ve Portfolyo Sitesi

**Burak Yiğit Solmaz** — Öğrenci No: **25350028**
Ankara Üniversitesi Nallıhan Meslek Yüksekokulu · Bilgisayar Programcılığı (2. sınıf)

PHP 8 + MySQL (MariaDB) ile geliştirilmiş, veritabanı destekli kişisel blog ve tanıtım sitesi.
Blog yazıları, kategoriler, etiketler, projeler ve yetenekler statik HTML yerine **veritabanından**
gelir; iletişim formu mesajları veritabanına kaydedilir ve içerik bir **yönetim paneli** üzerinden
yönetilir.

---

## Özellikler

**Site tarafı**
- Ana sayfa: giriş alanı, CV indirme, hakkımda metni, son 3 yazı, yetenek çubukları
- Yazılar: kategori filtresi, etiket filtresi, arama, sayfalama, aylık arşiv
- Yazı detayı: okunma sayacı, beğeni butonu, etiketler, ilgili yazılar
- Projeler: GitHub depoları veritabanından listelenir
- İletişim: form → `messages` tablosu; GitHub ve Instagram bağlantıları
- Karanlık / aydınlık tema, mobil uyumlu, 404 sayfası
- Modern arayüz: kod kartlı hero, kaydırma ilerleme çubuğu, yumuşak animasyonlar

**Yönetim paneli** (`/admin`)
- Özet ekranı: yazı, mesaj, ziyaret, beğeni, CV indirme sayıları + son işlemler
- Yazı yönetimi (ekle / düzenle / sil / yayından kaldır) ve etiket seçimi
- Kategori, etiket, proje ve yetenek yönetimi
- Gelen mesajları okuma, okundu işaretleme, silme
- Site ayarları (hakkımda metni, e-posta, GitHub, LinkedIn, Instagram, CV yolu)
- `activity_log` ile yönetici işlem günlüğü

---

## Gereksinimler

| Bileşen | Sürüm |
|---|---|
| XAMPP (Apache + MySQL/MariaDB) | 8.x |
| PHP | 8.0 ve üzeri (geliştirme: 8.3.36) |
| MySQL / MariaDB | 5.7+ / 10.4+ (geliştirme: MariaDB 10.11) |

Ek kütüphane, Composer veya framework **kullanılmadı** — saf PHP + PDO.

---

## Kurulum

### 1) Dosyaları yerleştir
Proje klasörü şu adreste olmalı:

```
C:\xampp\htdocs\kisisel-blog
```

### 2) XAMPP servislerini başlat
XAMPP Control Panel → **Apache** ve **MySQL** için *Start*.

### 3) Veritabanını oluştur
Sırayla iki dosyayı çalıştır:

**Yol A — phpMyAdmin ile (en kolay)**
1. `http://localhost/phpmyadmin` adresine gir
2. *SQL* sekmesine geç
3. `database/01_schema.sql` içeriğini yapıştır → *Git*
4. Aynı şekilde `database/02_seed.sql` içeriğini yapıştır → *Git*

**Yol B — komut satırı ile**
```bat
C:\xampp\mysql\bin\mysql.exe -u root -e "source C:/xampp/htdocs/kisisel-blog/database/01_schema.sql"
C:\xampp\mysql\bin\mysql.exe -u root -e "source C:/xampp/htdocs/kisisel-blog/database/02_seed.sql"
```

`01_schema.sql` veritabanını (`kisisel_blog_db`) kendisi oluşturur; elle oluşturmanız gerekmez.

### 4) Yapılandırma
`config/config.example.php` dosyasını `config/config.local.php` olarak kopyala ve gerekirse
veritabanı bilgilerini düzenle. `config.local.php` **git'e eklenmez** (`.gitignore`).

XAMPP varsayılan ayarlarıyla (`root` / boş şifre) olduğu gibi çalışır.

### 5) Aç
```
http://localhost/kisisel-blog/
```

### 6) Yönetim paneli
```
http://localhost/kisisel-blog/admin/
```
| Kullanıcı adı | Şifre |
|---|---|
| `admin` | `burak1` |

> Şifre veritabanında düz metin olarak tutulmaz; `password_hash()` ile üretilmiş bcrypt
> özeti saklanır. Değiştirmek için yeni özet üretip `admins` tablosunu güncelleyin:
> ```bat
> C:\xampp\php\php.exe -r "echo password_hash('yeni_sifre', PASSWORD_DEFAULT);"
> ```

---

## Klasör Yapısı

```
kisisel-blog/
├─ index.php              Ana sayfa
├─ yazilar.php            Yazı listesi (arama / filtre / sayfalama / arşiv)
├─ yazi.php               Yazı detayı
├─ projeler.php           GitHub depoları
├─ iletisim.php           İletişim formu
├─ like.php               Beğeni uç noktası (POST)
├─ download.php           CV indirme + sayaç
├─ 404.php                Bulunamadı sayfası
├─ .htaccess              Apache ayarları
├─ config/
│  ├─ config.php          Yapılandırma yükleyici
│  ├─ config.local.php    Gerçek bilgiler (git dışı)
│  └─ config.example.php  Şablon
├─ includes/
│  ├─ db.php              PDO bağlantısı
│  ├─ functions.php       Yardımcı fonksiyonlar
│  ├─ header.php          Üst şablon
│  └─ footer.php          Alt şablon
├─ admin/
│  ├─ login.php, logout.php
│  ├─ index.php           Özet paneli
│  ├─ yazilar.php, yazi-form.php
│  ├─ kategoriler.php, etiketler.php
│  ├─ projeler.php, yetenekler.php
│  ├─ mesajlar.php, ayarlar.php
│  ├─ includes/           auth.php + panel şablonları
│  └─ assets/admin.css    Panel stili
├─ css/style.css          Site stili
├─ js/main.js             Tema, menü, form doğrulaması
├─ assets/cv/             CV (PDF)
├─ uploads/               Yüklenen dosyalar
└─ database/
   ├─ 01_schema.sql       Tablolar + view + procedure
   ├─ 02_seed.sql         Örnek veriler
   ```

---

## Veritabanı Şeması

13 tablo, InnoDB, `utf8mb4_unicode_ci`.

| Tablo | Görevi |
|---|---|
| `admins` | Yönetici hesapları (bcrypt özet) |
| `categories` | Blog kategorileri |
| `articles` | Blog yazıları (kategori FK, okunma sayacı) |
| `tags` | Etiketler |
| `article_tags` | Yazı ↔ etiket köprü tablosu (**çoktan-çoğa**) |
| `projects` | GitHub depoları |
| `skills` | Yetenekler ve yüzdeler |
| `messages` | İletişim formu mesajları |
| `settings` | Site ayarları (anahtar/değer) |
| `article_likes` | Beğeniler (`UNIQUE(article_id, ip_hash)`) |
| `visits` | Sayfa ziyaretleri (istatistik) |
| `activity_log` | Yönetici işlem günlüğü |
| `downloads` | CV indirme sayacı |

**Ek SQL nesneleri**
- `v_article_list` — yayınlanmış yazılar + kategori bilgisi (**VIEW**)
- `sp_article_search(term)` — başlık/özet/içerikte arama (**STORED PROCEDURE**)

İlişkiler yabancı anahtarlarla (`ON DELETE CASCADE` / `SET NULL`) korunur.

---

## Güvenlik

| Konu | Uygulama |
|---|---|
| SQL Injection | Tüm sorgular PDO **prepared statement**, `ATTR_EMULATE_PREPARES = false` |
| XSS | Tüm çıktılar `e()` = `htmlspecialchars(ENT_QUOTES, 'UTF-8')` |
| CSRF | Formlarda `csrf_token()` / `csrf_check()` (`hash_equals`) |
| Şifre | `password_hash()` + `password_verify()` |
| Oturum | `session_regenerate_id(true)` ile girişte kimlik yenileme |
| Kaba kuvvet | 5 hatalı denemede 60 saniye bekleme |
| Form spam | Honeypot alanı + IP başına 60 saniye hız limiti |
| Kişisel veri | IP adresi **düz saklanmaz**, `hash('sha256', ip + salt)` |
| Sayaç şişirme | Okunma oturum başına 1 kez; beğeni `UNIQUE` kısıtla |
| Dosya erişimi | CV yolu koddan gelir; `config/`, `database/`, `includes/` Apache'de kapalı |

---

## Sistem Yapısı ve Doğrulama

Sistemin uçtan uca çalışabilirliği ve dinamik veritabanı entegrasyonu aşağıdaki akış üzerinden kontrol edilmektedir:

1. İletişim Formu ve Canlı Veri Akışı
İletişim formu üzerinden gönderilen mesajlar veritabanındaki messages tablosuna anlık olarak işlenmektedir. Yönetim paneli (/admin) üzerindeki Mesajlar sekmesinde gelen veriler "Yeni" durumuyla listelenmekte ve Özet ekranındaki dinamik mesaj sayacı otomatik olarak güncellenmektedir.

2. İçerik Yönetimi (CRUD İşlemleri)
Admin paneli üzerinden eklenen yeni blog yazıları ön yüzde eş zamanlı olarak yayına girmektedir. Yayından kaldırılan veya silinen içerikler anında listeden düşmektedir.

3. Veritabanı Mimarisi ve Sorgular
Proje veritabanında karmaşık sorgular için JOIN ve GROUP BY yapıları kullanılmış; veri bütünlüğü ve raporlama için VIEW ve PROCEDURE tanımlamaları entegre edilmiştir. Tüm veritabanı şeması database/01_schema.sql dosyası içerisinde tanımlıdır.

---

## Sık Karşılaşılan Sorunlar ve Çözümleri

| Belirti | Çözüm |
|---|---|
| "Veritabanı bağlantısına erişilemiyor" | XAMPP üzerinde MySQL servisinin aktif olduğunu ve config.local.php bilgilerini kontrol ediniz. |
| http://localhost/ açılıyor ama site 404 veriyor | Proje klasör adının kisisel-blog olduğunu ve config.local.php içindeki base_url tanımını doğrulayınız. |
| Türkçe karakterler bozuk | Veritabanı karşılaştırma karakter setinin utf8mb4_unicode_ci ve SQL dosyalarının UTF-8 formatında olduğundan emin olunuz. |
| 80 port çakışması (Apache başlamıyor) | XAMPP -> Config -> httpd.conf dosyasından Listen 80 satırını 8080 olarak güncelleyiniz. |
| CSS / JS yüklenmiyor | Projenin htdocs dizininde yer aldığını ve base_url yolunu kontrol ediniz. |

---

## Teknik Notlar

- Sunucu Gereksinimi: Bu proje dinamik PHP mimarisi ve MySQL veritabanı bağımlılıkları barındırdığından XAMPP (Apache + MySQL) ortamında çalışmaktadır. Statik sunucular veya GitHub Pages üzerinde PHP çalıştırılamaz.
- Güvenlik ve .gitignore: Hassas veritabanı erişim bilgilerini içeren config/config.local.php ve kullanıcı tarafından yüklenen uploads/ klasör içeriği .gitignore ile versiyon kontrolü dışında tutulmuştur.
- Varlık Yönetimi: Tarayıcı konsol hatalarını önlemek ve dosya yollarını korumak adına css/ ve js/ klasör isimleri ve hiyerarşisi korunmuştur.
- Veritabanı Kurulumu: Projenin veritabanı yapısını oluşturmak için gerekli SQL betikleri database/*.sql klasöründe yer almaktadır.

---

© 2026 Burak Yiğit Solmaz — Kişisel Blog ve Yönetim Paneli Projesi