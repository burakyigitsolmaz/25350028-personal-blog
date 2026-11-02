-- ============================================================
--  Kişisel Blog — Örnek Veriler (Seed)
--  Önce 01_schema.sql çalıştırılmış olmalı.
--  Komut satırı:
--    mysql -u root -e "source C:/xampp/htdocs/kisisel-blog/database/02_seed.sql"
-- ============================================================

SET NAMES utf8mb4;
USE `kisisel_blog_db`;

-- ------------------------------------------------------------
-- Yönetici  (kullanıcı adı: admin / şifre: burak1)
-- Şifre düz metin olarak TUTULMAZ; password_hash() çıktısıdır.
-- ------------------------------------------------------------
INSERT INTO `admins` (`username`, `password_hash`) VALUES
('admin', '$2y$10$vn3exObEKKezX6LGveWXwej8mXF8uNWqYU9I1K6FoNOHSe30g9aK6');

-- ------------------------------------------------------------
-- Kategoriler
-- ------------------------------------------------------------
INSERT INTO `categories` (`name`, `slug`) VALUES
('Web Geliştirme',  'web-gelistirme'),
('Programlama',     'programlama'),
('Veritabanı',      'veritabani'),
('Oyun Geliştirme', 'oyun-gelistirme'),
('Python',          'python'),
('Frontend',        'frontend');

-- ------------------------------------------------------------
-- Etiketler
-- ------------------------------------------------------------
INSERT INTO `tags` (`name`, `slug`) VALUES
('HTML',            'html'),
('CSS',             'css'),
('JavaScript',      'javascript'),
('Erişilebilirlik', 'erisilebilirlik'),
('SEO',             'seo'),
('Responsive',      'responsive'),
('C',               'c'),
('Bellek Yönetimi', 'bellek-yonetimi'),
('SQL',             'sql'),
('Normalizasyon',   'normalizasyon'),
('Unity',           'unity'),
('C#',              'csharp'),
('Python',          'python'),
('Blender',         'blender');

-- ------------------------------------------------------------
-- Blog yazıları (6 adet)
-- ------------------------------------------------------------
INSERT INTO `articles`
  (`category_id`, `title`, `slug`, `summary`, `content`, `view_count`, `is_published`, `created_at`)
VALUES
(
  (SELECT `id` FROM `categories` WHERE `slug` = 'web-gelistirme'),
  'Semantik HTML ile Erişilebilir Web Sayfaları',
  'semantik-html-ile-erisilebilir-web-sayfalari',
  'HTML5''in semantik etiketlerinin, sayfa yapısını hem tarayıcı hem de ekran okuyucular için nasıl anlamlı hale getirdiğini inceliyorum.',
  'Web geliştirmede görsel sonuç kadar kullanılan yapı da önemlidir. HTML5 ile gelen header, nav, main, section, article ve footer gibi semantik etiketler, sayfanın bölümlerini tarayıcılar ve arama motorları için anlamlı hale getirir. Eski yöntemde her şey div etiketleriyle sarılırken, bugün hangi bloğun ne işe yaradığı kodun kendisinden okunabilmektedir.

Semantik yapının en somut kazanımlarından biri erişilebilirliktir. Ekran okuyucu kullanan bir kullanıcı, sayfayı başlıklar ve landmark''ları üzerinden gezebilir. Doğru etiketlenmiş bir form, ilgili input alanlarıyla etiketleri arasındaki bağlantıyla birlikte algılanır; bu da label ve aria özniteliklerinin doğru kullanılmasını zorunlu kılar.

Akademik bir projede ya da kurumsal bir sitede bu kuralları uygulamak, sonradan büyük düzeltmalar yapmayı gerektirmez. Kod ilk günden itibaren düzenli kalır, CSS seçicileri sadeleşir ve bakım maliyeti düşer. Ayrıca semantik HTML, SEO açısından da arama motorlarının içeriği daha isabetli indekslemesini destekler.

Sonuç olarak semantik HTML yalnızca teknik bir tercih değil, web''i herkes için kullanılabilir kılan bir sorumluluktur. Küçük bir projede bile doğrudan bu standartlarla başlamak, önümüzdeki dönemde üzerine inşa edilecek her yeni özelliği kolaylaştıracaktır.',
  0, 1, '2026-10-12 10:00:00'
),
(
  (SELECT `id` FROM `categories` WHERE `slug` = 'programlama'),
  'C Dilinde İşaretçiler ve Bellek Yönetimi',
  'c-dilinde-isaretciler-ve-bellek-yonetimi',
  'İşaretçi yapısının çalışma mantığı, dinamik bellek ayırma ve yaygın bellek hatalarından korunma yöntemlerine dair teknik bir değerlendirme.',
  'C dilinde bir işaretçi, bellekteki bir adres değeri saklayan değişkendir. Başlangıçta soyut görünse de temel mantık basittir: değişken artık değerin kendisiyle değil, değerin bulunduğu konumla ilgilenir. Bu ayrım, fonksiyonlara referans yoluyla veri aktarmak ve dinamik veri yapıları kurmak için gereklidir.

malloc ve calloc fonksiyonlarıyla program çalışma zamanında bellek ayrılabilir. Ayrılan bu bellek kullanım bittiğinde free ile iade edilmelidir. free edilmeyen bellek sızıntılara, tekrar free edilen bellek ise tanımsız davranışlara yol açar. İşaretçinin NULL''a eşit olup olmadığını başlangıçta kontrol etmek, çoğu çökme senaryosunu daha baştan engeller.

Yaygın hatalar arasında başlatılmamış işaretçilerin kullanımı, dizi sınırlarının aşılması ve fonksiyonun döndüğü yerel değişkenin adresinin saklanması sayılabilir. Derleyici uyarılarının ciddiye alınması ve valgrind gibi araçlarla düzenli kontrol, bu hataların fark edilmesini kolaylaştırır.

Kısacası C dilinde bellek yönetimi bir disiplin meselesidir. Temel kurallar otomatikleştirildiğinde, işaretçiler karmaşık bir sorun değil, dilin sunduğu en güçlü araç haline gelir. Bu konudaki sağlam temel, veri yapıları derslerinde ve sistem programlamada doğrudan karşılığını bulur.',
  0, 1, '2026-09-28 10:00:00'
),
(
  (SELECT `id` FROM `categories` WHERE `slug` = 'veritabani'),
  'SQL''de Normalizasyon İlkeleri ve Veri Bütünlüğü',
  'sqlde-normalizasyon-ilkeleri-ve-veri-butunlugu',
  'İlişkisel veritabanı tasarımında normal formların önemi ve veri tekrarını önleyerek bütünlüğü nasıl koruyabileceğimiz ele alınıyor.',
  'İlişkisel veritabanı tasarımında normalizasyon, verileri mantıksal olarak ayrı tablolar arasında düzenleme sürecidir. Amaç, aynı bilginin birden fazla yerde tutulmasını engellemek ve kayıt ekleme, güncelleme ile silme işlemlerinde ortaya çıkabilecek tutarsızlıkları ortadan kaldırmaktır.

Birinci normal form, her alanın tek bir değer barındırmasını ister. İkinci normal form, bileşik anahtar dışındaki sütunların anahtarın tamamına değil yalnızca bir kısmına bağımlı olmasını engeller. Üçüncü normal form ise anahtar olmayan sütunlar arasındaki geçişli bağımlılıkları kaldırır. Uygulamada çoğu kurumsal proje için üçüncü normal form yeterli bir referans noktasıdır.

Normalizasyonun yan etkisi, sık sorgularda birden fazla tablonun birleştirilmesi gereğidir. Yoğun raporlama senaryolarında performans için kontrollü bir denormalizasyon tercih edilebilir; ancak bu karar ölçümle desteklenmeli, yapı bozulmadan ilgili kısıt ve tetikleyicilerle veri bütünlüğü korunmalıdır.

Yabancı anahtar kısıtları, benzersizlik kısıtları ve uygun veri tipleriyle birlikte normalizasyon, veritabanının uzun ömürlü olmasını sağlar. İlk gereksinimler değişse bile doğru ayrıştırılmış bir şema, yeni tabloların mevcut yapıya hasarsız eklenmesine imkân tanır.',
  0, 1, '2026-09-05 10:00:00'
),
(
  (SELECT `id` FROM `categories` WHERE `slug` = 'oyun-gelistirme'),
  'Unity''de Karakter Kontrolü ve Fizik Sistemi',
  'unityde-karakter-kontrolu-ve-fizik-sistemi',
  'Unity motorunda Rigidbody ve CharacterController bileşenleriyle sağlıklı bir karakter hareketi kurmanın yollarını değerlendiriyorum.',
  'Unity''de karakter hareketini kurmak için iki temel yaklaşım vardır: Rigidbody bileşeniyle fizik motoruna dayalı hareket ve CharacterController ile daha kontrollü, fiziksiz hareket. Üçüncü şahıs veya birinci şahıs oyunlarda, platformerdeki gibi hassas kontrol isteniyorsa CharacterController çoğu zaman daha öngörülebilir sonuç verir.

Rigidbody tercih edildiğinde, hızı doğrudan transform üzerinden değil, FixedUpdate içinde rigidbody.velocity ya da AddForce ile değiştirmek gerekir. Update fonksiyonunda yapılan fizik girdi okuması ile FixedUpdate içindeki uygulama ayrımını korumak, çerçeve hızına bağımlı titremeleri büyük ölçüde engeller.

Yer teması kontrolü karakter sıçraması için kritiktir. Raycast veya Physics.CheckSphere ile ayağın temas yüzeyiyle mesafesi ölçülür; kontak sayısına güvenmek yamaçlarda yanıltıcı olabilir. Eğim limiti ve adım yüksekliği değerleri, karakterin hangi yüzeylere çıkacağını doğrudan belirler.

Sağlıklı bir karakter kontrolü, küçük modüler bileşenlerden oluşur: giriş yönetimi, hareket, sıçrama ve etkileşim ayrı kod parçalarında tutulduğunda hem okunabilirlik artar hem de yeni karakter tipleri için aynı temel yeniden kullanılabilir. Bu yaklaşım akademik projelerde de okunabilirlik ve yeniden kullanılabilirlik açısından belirgin avantaj sağlar.',
  0, 1, '2026-08-17 10:00:00'
),
(
  (SELECT `id` FROM `categories` WHERE `slug` = 'python'),
  'Python ile Blender Otomasyonu',
  'python-ile-blender-otomasyonu',
  'Blender''ın bpy modülünü kullanarak üç boyutlu sahne ve ışık kurulumlarını betikle otomatikleştirme deneyimimi aktarıyorum.',
  'Blender, Python betikleriyle tam kapsamlı bir otomasyon ortamı sağlar. bpy modülü üzerinden sahne oluşturma, mesh ekleme, malzeme atama ve kamera düzenleme işlemleri programatik olarak yürütülebilir. Bu sayede yüzlerce nesnenin tek tek elle konumlanması yerine, döngülerle dakikalar içinde düzenli sahneler kurulabilir.

Temel iş akışı şöyledir: bpy.data üzerinden yeni bir mesh ve object oluşturulur, collection''a bağlanır, transform değerleri programatik olarak ayarlanır. Bir stüdyo ışığı kurmak için ışık tipine ve erişim mesafesine göre parametreler belirlenir; üç noktalı ışık düzeneği klasik bir başlangıç şablonudur.

Otomasyonun asıl gücü, parametrik çalışabilmesinden gelir. Örneğin farklı renk paletlerinde aynı sahnenin onlarca varyantını render ederek gözden geçirmek, elle yapılması halinde saatler süren işi dakikaya indirir. Betiklerin ekranın Scripting sekmesinden veya komut satırından çalıştırılabileceği unutulmamalıdır.

Bu yöntem, modelleme odağın dışına çıkan teknik sahnelerde özellikle verimlidir. Zaman içinde yazılan yardımcı fonksiyonlar küçük bir kişisel kütüphaneye dönüşür ve her yeni projede başlangıç yükünü azaltır.',
  0, 1, '2026-07-30 10:00:00'
),
(
  (SELECT `id` FROM `categories` WHERE `slug` = 'frontend'),
  'CSS Grid ile Modern Duyarlı Düzenler',
  'css-grid-ile-modern-duyarli-duzenler',
  'Flexbox''a alternatif olarak CSS Grid''in sunduğu iki boyutlu yerleşim gücünü ve medya sorgularıyla birlikte nasıl kullanıldığını anlatıyorum.',
  'CSS Grid, sayfa düzenini satır ve sütun eksenlerinde birlikte tanımlamayı sağlayan iki boyutlu bir yerleşim sistemidir. Flexbox tek eksende üstünlük sağlarken, kart galerisi ya da karmaşık arayüzler gibi hem satır hem sütun gerektiren düzenlerde Grid açık biçimde öne çıkar.

grid-template-columns ile tanımlanan repeat(auto-fit, minmax(300px, 1fr)) kalıbı, ekran genişliğine göre sütun sayısını otomatik ayarlar. Bu tek satır, çoğu kart düzeni için ayrı medya sorguları yazma ihtiyacını ortadan kaldırır. gap ile boşluk kontrolü float tabanlı düzenlerdeki negatif kenar boşluğu hack''lerine gerek bırakmaz.

Daha karmaşık sayfalarda grid-template-areas kullanılabilir: header, sidebar, main ve footer gibi bölümler isimli alanlara atanır ve her breakpoint için şema yeniden çizilir. Kod okuma sırasında yerleşimin tamamının tek bakışta algılanması, ekip çalışmalarında hata ayıklamayı hızlandırır.

Grid, flexbox''ın tamamlayıcısı olarak tüm modern tarayıcılarda standartlaşmıştır. Doğru yaklaşım ikili kullanımdır: genel yerleşim Grid ile, içindeki bileşen dizilimleri Flexbox ile kurulduğunda her iki sistemin de gücünden yararlanılır.',
  0, 1, '2026-07-08 10:00:00'
);

-- ------------------------------------------------------------
-- Yazı ↔ Etiket bağlantıları (çoktan-çoğa)
-- ------------------------------------------------------------
INSERT INTO `article_tags` (`article_id`, `tag_id`)
SELECT a.`id`, t.`id`
FROM `articles` a
JOIN `tags` t ON t.`slug` IN ('html', 'erisilebilirlik', 'seo')
WHERE a.`slug` = 'semantik-html-ile-erisilebilir-web-sayfalari';

INSERT INTO `article_tags` (`article_id`, `tag_id`)
SELECT a.`id`, t.`id`
FROM `articles` a
JOIN `tags` t ON t.`slug` IN ('c', 'bellek-yonetimi')
WHERE a.`slug` = 'c-dilinde-isaretciler-ve-bellek-yonetimi';

INSERT INTO `article_tags` (`article_id`, `tag_id`)
SELECT a.`id`, t.`id`
FROM `articles` a
JOIN `tags` t ON t.`slug` IN ('sql', 'normalizasyon')
WHERE a.`slug` = 'sqlde-normalizasyon-ilkeleri-ve-veri-butunlugu';

INSERT INTO `article_tags` (`article_id`, `tag_id`)
SELECT a.`id`, t.`id`
FROM `articles` a
JOIN `tags` t ON t.`slug` IN ('unity', 'csharp')
WHERE a.`slug` = 'unityde-karakter-kontrolu-ve-fizik-sistemi';

INSERT INTO `article_tags` (`article_id`, `tag_id`)
SELECT a.`id`, t.`id`
FROM `articles` a
JOIN `tags` t ON t.`slug` IN ('python', 'blender')
WHERE a.`slug` = 'python-ile-blender-otomasyonu';

INSERT INTO `article_tags` (`article_id`, `tag_id`)
SELECT a.`id`, t.`id`
FROM `articles` a
JOIN `tags` t ON t.`slug` IN ('css', 'responsive')
WHERE a.`slug` = 'css-grid-ile-modern-duyarli-duzenler';

-- ------------------------------------------------------------
-- Projeler (GitHub depoları)
-- ------------------------------------------------------------
INSERT INTO `projects`
  (`title`, `description`, `tech_stack`, `repo_url`, `sort_order`, `is_visible`)
VALUES
(
  'web-development-projects',
  'HTML, CSS ve JavaScript ile hazırladığım proje ve alıştırmaların toplandığı depo. Arayüz denemeleri ve küçük web uygulamaları burada sürüm kontrolüyle tutuluyor.',
  'HTML5 · CSS3 · JavaScript',
  'https://github.com/burakyigitsolmaz/web-development-projects',
  1, 1
),
(
  'cpp-projects',
  'C++ dilinde yazdığım pratik projeler ve ders çalışmaları. Algoritma, veri yapıları ve nesne yönelimli programlama örnekleri yer alıyor.',
  'C++',
  'https://github.com/burakyigitsolmaz/cpp-projects',
  2, 1
),
(
  'NBP124 — Veritabanı Yönetim Sistemleri',
  'Veritabanı yönetim sistemleri dersi kapsamında hazırladığım ödev ve mini projeler. İlişkisel tasarım, normalizasyon ve SQL sorguları üzerine çalışmalar.',
  'SQL · MySQL · Veritabanı Tasarımı',
  'https://github.com/burakyigitsolmaz/NBP124-DataBaseManagementSystem-BurakYIGITSolmaz',
  3, 1
),
(
  'NBP134 — Web Arayüz Tasarımı',
  'Web arayüz tasarımı ve programlama dersi kapsamında hazırladığım ödev ve mini proje. Duyarlı (responsive) arayüz çalışmaları içeriyor.',
  'HTML5 · CSS3 · Duyarlı Tasarım',
  'https://github.com/burakyigitsolmaz/NBP134-FundamentalsofWebInterfaceDesignandProgramming-BurakYIGITSolmaz',
  4, 1
),
(
  '25350028 — Kişisel Blog',
  '2 yıllık öğrenimimi kapsayan, PHP 8 ve MySQL ile geliştirilmiş full stack kişisel blog sitesi. Veritabanı destekli içerik yönetimi, yönetim paneli ve responsive arayüz içeriyor.',
  'PHP · MySQL · HTML5 · CSS3 · JavaScript',
  'https://github.com/burakyigitsolmaz/25350028-Kisisel-Blog',
  0, 1
);

-- ------------------------------------------------------------
-- Yetenekler
-- ------------------------------------------------------------
INSERT INTO `skills` (`name`, `level`, `group_name`, `sort_order`) VALUES
('C / C++',          75, 'Diller',       1),
('C#',               70, 'Diller',       2),
('HTML5 / CSS3',     85, 'Diller',       3),
('JavaScript',       72, 'Diller',       4),
('Python',           80, 'Diller',       5),
('MySQL',            70, 'Veritabanı',   1),
('MS SQL Server',    65, 'Veritabanı',   2),
('Unity 6',          68, 'Araçlar',      1),
('Blender (bpy)',    60, 'Araçlar',      2),
('Git / GitHub',     66, 'Araçlar',      3);

-- ------------------------------------------------------------
-- Site ayarları
-- ------------------------------------------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_title',       'Burak Yiğit Solmaz | Kişisel Blog'),
('site_title_short', 'Burak Yiğit Solmaz'),
('site_description', 'Burak Yiğit Solmaz — Kişisel blog ve tanıtım sitesi. Ankara Üniversitesi Nallıhan Meslek Yüksekokulu Bilgisayar Programcılığı öğrencisi.'),
('hero_text',        'Merhaba, ben Burak Yiğit Solmaz. Ankara Üniversitesi Nallıhan Meslek Yüksekokulu Bilgisayar Programcılığı programında öğrenimime devam ediyorum. Bu sitede yazılım ve teknoloji alanındaki çalışmalarımı, deneyimlerimi ve görüşlerimi resmi bir dille sizlerle paylaşıyorum.'),
('about_text',       'Ben Burak Yiğit Solmaz, Ankara Üniversitesi Nallıhan Meslek Yüksekokulu Bilgisayar Programcılığı programının ikinci sınıf öğrencisiyim. Eğitimim boyunca temel programlama ilkeleri, algoritma geliştirme ve veri yapıları konularında sistemli bir şekilde çalışmaktayım. Akademik bilgilerimi pratik projelere dönüştürmeyi hedefliyor, bu doğrultuda kişisel çalışmalarıma düzenli olarak devam ediyorum.\n\nBaşlıca ilgi alanlarım arasında web geliştirme, C/C++ ve C# programlama dilleri, Python betikleştirme ve veritabanı yönetim sistemleri yer almaktadır. Ayrıca Unity motoru ile oyun geliştirme ve Blender üzerinden Python gibi üç boyutlu modelleme süreçlerini otomatikleştirme konularında da kendimi geliştirmekteyim. Öğrendiğim her yeni konuyu bu blogda teknik ama anlaşılır bir dille aktarmayı amaçlıyorum.'),
('email',            'solmazburakss@gmail.com'),
('github_url',       'https://github.com/burakyigitsolmaz'),
('linkedin_url',     'https://www.linkedin.com/in/burakyigitsolmaz/'),
('instagram_url',    'https://www.instagram.com/burakyigitsolmaz'),
('cv_path',          'assets/cv/Burak_Yigit_Solmaz_CV.pdf'),
('location',         'Ankara, Türkiye'),
('student_no',       '25350028');

SELECT '02_seed.sql tamamlandi' AS `durum`;
