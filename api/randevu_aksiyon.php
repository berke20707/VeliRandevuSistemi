<?php
header('Content-Type: application/json; charset=utf-8');

session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum bulunamadı.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$islem = $input['islem'] ?? '';

$veli_id = $_SESSION['user_id'];

// Aktif randevuyu bul
$stmt = $db->prepare("SELECT * FROM randevular WHERE veli_id = ? AND durum IN ('bekliyor', 'onaylandi') ORDER BY tarih ASC, saat ASC LIMIT 1");
$stmt->execute([$veli_id]);
$randevu = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$randevu) {
    echo json_encode(['success' => false, 'message' => 'Aktif randevunuz bulunamadı.']);
    exit;
}

$ogretmen_ad = $randevu['ogretmen_ad'];
$tarih_saat_str = date('d.m.Y', strtotime($randevu['tarih'])) . " Saat: " . $randevu['saat'];

// Öğretmen ID'sini bul (users tablosunda adı soyadı öğretmenin adıyla eşleşen)
$ogretmen_stmt = $db->prepare("SELECT id FROM users WHERE CONCAT(adi, ' ', soyadi) = ? AND rol = 'ogretmen' LIMIT 1");
$ogretmen_stmt->execute([$ogretmen_ad]);
$ogretmen_user = $ogretmen_stmt->fetch();

$ogretmen_id = $ogretmen_user ? $ogretmen_user['id'] : 0;

$baslik = "";
$mesaj = "";

if ($islem === 'vardim') {
    $baslik = "Veli Geldi: " . $randevu['veli_ad_soyad'];
    $mesaj = $randevu['ogrenci_ad'] . " velisi okula giriş yaptı. Randevu zamanı: " . $tarih_saat_str;
} else if ($islem === 'sos') {
    $baslik = "Gecikme Bildirimi: " . $randevu['veli_ad_soyad'];
    $mesaj = $randevu['ogrenci_ad'] . " velisi 15 dakika kadar gecikeceğini bildirdi. Randevu: " . $tarih_saat_str;
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz işlem.']);
    exit;
}

// Güvenliğe de bildirim atılabilir mantıken, ama sadece öğretmene atıyoruz şimdilik.
if ($ogretmen_id > 0) {
    try {
        $bildirim_sql = $db->prepare("INSERT INTO notifications (user_id, baslik, mesaj, okundu_mu) VALUES (?, ?, ?, 0)");
        $bildirim_sql->execute([$ogretmen_id, $baslik, $mesaj]);
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
} else {
    // Öğretmen DB'de kayıtlı değilse bile başarılı dön
    echo json_encode(['success' => true]);
}
?>
