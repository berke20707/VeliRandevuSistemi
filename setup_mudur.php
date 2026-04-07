<?php
/**
 * Müdür Hesabı Kurulum Scripti
 * Bu dosyayı tarayıcıda bir kez çalıştırın: http://localhost/randevusistemi/setup_mudur.php
 */
require_once 'config/database.php';

echo "<pre style='background:#0B132B; color:#5BC0BE; padding:30px; font-family:monospace; font-size:14px;'>";

// 1. users tablosuna kullanici_adi kolonu (yoksa ekle)
try {
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS kullanici_adi VARCHAR(100) DEFAULT NULL");
    echo "✅ users.kullanici_adi kolonu kontrol edildi.\n";
} catch(Exception $e) {
    echo "⚠️ kullanici_adi: " . $e->getMessage() . "\n";
}

// 2. Eski sakdag kaydını temizle
$db->exec("DELETE FROM users WHERE kullanici_adi = 'sakdag' AND rol = 'yonetici'");
$db->exec("DELETE FROM users WHERE tc_kimlik = 'sakdag' AND rol = 'yonetici'");
echo "✅ Eski 'sakdag' kayıtları temizlendi.\n";

// 3. Mevcut serkanakdag kullanıcısını kontrol et
$check = $db->prepare("SELECT id FROM users WHERE kullanici_adi = 'serkanakdag'");
$check->execute();
$mevcut = $check->fetch();

$sifre_hash = password_hash('sakdag59', PASSWORD_DEFAULT);

if ($mevcut) {
    $db->prepare("UPDATE users SET adi='Serkan', soyadi='AKDAĞ', sifre=?, rol='yonetici', silindi_mi=0 WHERE id=?")->execute([$sifre_hash, $mevcut['id']]);
    echo "✅ Mevcut müdür hesabı güncellendi. (ID: {$mevcut['id']})\n";
} else {
    $db->prepare("INSERT INTO users (adi, soyadi, kullanici_adi, tc_kimlik, sifre, rol, email, silindi_mi) VALUES ('Serkan', 'AKDAĞ', 'serkanakdag', 'serkanakdag', ?, 'yonetici', 'mudur@ahievran.edu.tr', 0)")->execute([$sifre_hash]);
    echo "✅ Yeni müdür hesabı oluşturuldu.\n";
}

// 4. announcements tablosu
$db->exec("CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    content TEXT,
    hedef VARCHAR(50) DEFAULT 'hepsi',
    is_active TINYINT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
try { $db->exec("ALTER TABLE announcements ADD COLUMN IF NOT EXISTS hedef VARCHAR(50) DEFAULT 'hepsi'"); } catch(Exception $e) {}
echo "✅ announcements tablosu kontrol edildi.\n";

// 5. blacklist tablosu
$db->exec("CREATE TABLE IF NOT EXISTS blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tc_kimlik VARCHAR(30) NOT NULL,
    ad_soyad VARCHAR(200) NOT NULL,
    sebep TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "✅ blacklist tablosu kontrol edildi.\n";

// 6. settings tablosu
$db->exec("CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "✅ settings tablosu kontrol edildi.\n";

// 7. branches tablosu
$db->exec("CREATE TABLE IF NOT EXISTS branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "✅ branches tablosu kontrol edildi.\n";

// 8. notifications tablosu
$db->exec("CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    baslik VARCHAR(500),
    icerik TEXT,
    okundu_mu TINYINT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "✅ notifications tablosu kontrol edildi.\n";

// 9. Varsayılan ayarlar
$defaults = [
    'appointment_hours' => '09:00, 09:50, 10:40, 11:30, 12:20, 13:50, 14:40',
    'holidays' => '2026-01-01, 2026-04-23, 2026-05-01, 2026-05-19, 2026-07-15, 2026-08-30, 2026-10-29'
];
foreach($defaults as $k => $v) {
    $c = $db->query("SELECT id FROM settings WHERE setting_key='$k'")->fetchColumn();
    if(!$c) {
        $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)")->execute([$k, $v]);
    }
}
echo "✅ Varsayılan sistem ayarları kontrol edildi.\n";

// 10. Varsayılan branşlar
$brans_sayisi = $db->query("SELECT COUNT(*) FROM branches")->fetchColumn();
if($brans_sayisi == 0) {
    $varsayilan_branslar = ['Bilişim Teknolojileri', 'Matematik', 'Fizik', 'Kimya', 'Türk Dili ve Edebiyatı', 'Tarih', 'Beden Eğitimi', 'İngilizce', 'Elektrik-Elektronik', 'Makine Teknolojisi'];
    foreach($varsayilan_branslar as $b) {
        $db->prepare("INSERT INTO branches (name) VALUES (?)")->execute([$b]);
    }
    echo "✅ Varsayılan branşlar eklendi.\n";
}

echo "\n====================================\n";
echo "🎉 Kurulum tamamlandı!\n";
echo "====================================\n";
echo "\nGiriş bilgileri:\n";
echo "  → Öğrenci sekmesinden giriş yapın\n";
echo "  → Kullanıcı Adı: serkanakdag\n";
echo "  → Şifre: sakdag59\n";
echo "\n⚠️ Bu dosyayı çalıştırdıktan sonra siliniz.\n";
echo "</pre>";
?>
