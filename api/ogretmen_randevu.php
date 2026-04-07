<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'ogretmen') {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$islem = $input['islem'] ?? '';
$randevu_id = (int)($input['randevu_id'] ?? 0);

$ogretmen_id = $_SESSION['user_id'];
$ogretmen_ad = $_SESSION['ad_soyad'];

// Randevuyu doğrula - bu öğretmenin randevusu mu?
$stmt = $db->prepare("SELECT * FROM randevular WHERE id = ? AND ogretmen_ad = ?");
$stmt->execute([$randevu_id, $ogretmen_ad]);
$randevu = $stmt->fetch();

if (!$randevu) {
    echo json_encode(['success' => false, 'message' => 'Randevu bulunamadı veya yetkiniz yok.']);
    exit;
}

try {
    if ($islem === 'onayla') {
        // Randevuyu onayla
        $db->prepare("UPDATE randevular SET durum = 'onaylandi' WHERE id = ?")->execute([$randevu_id]);
        
        // Veliye bildirim gönder
        if ($randevu['veli_id']) {
            $baslik = "Randevunuz Onaylandı ✓";
            $mesaj = $ogretmen_ad . " öğretmen, " . date('d.m.Y', strtotime($randevu['tarih'])) . " tarihli saat " . $randevu['saat'] . " randevunuzu onayladı.";
            $db->prepare("INSERT INTO notifications (user_id, baslik, mesaj, okundu_mu) VALUES (?, ?, ?, 0)")->execute([$randevu['veli_id'], $baslik, $mesaj]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Randevu başarıyla onaylandı.']);
        
    } elseif ($islem === 'reddet') {
        $mazeret = $input['mazeret'] ?? '';
        $alternatif_tarih = $input['alternatif_tarih'] ?? '';
        $alternatif_saat = $input['alternatif_saat'] ?? '';
        
        // Randevuyu reddet
        $db->prepare("UPDATE randevular SET durum = 'reddedildi' WHERE id = ?")->execute([$randevu_id]);
        
        // Veliye bildirim gönder
        if ($randevu['veli_id']) {
            $baslik = "Randevunuz Reddedildi";
            $mesaj = $ogretmen_ad . " öğretmen, " . date('d.m.Y', strtotime($randevu['tarih'])) . " tarihli randevunuzu reddetti.";
            if (!empty($mazeret)) {
                $mesaj .= "\nMazeret: " . $mazeret;
            }
            if (!empty($alternatif_tarih) && !empty($alternatif_saat)) {
                $mesaj .= "\n\n📅 Alternatif Öneri: " . date('d.m.Y', strtotime($alternatif_tarih)) . " tarihinde saat " . $alternatif_saat . " uygun olabilir. Yeni randevu talebinde bulunabilirsiniz.";
            }
            $db->prepare("INSERT INTO notifications (user_id, baslik, mesaj, okundu_mu) VALUES (?, ?, ?, 0)")->execute([$randevu['veli_id'], $baslik, $mesaj]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Randevu reddedildi ve veliye bildirim gönderildi.']);
        
    } elseif ($islem === 'tamamla') {
        $db->prepare("UPDATE randevular SET durum = 'tamamlandi' WHERE id = ?")->execute([$randevu_id]);
        echo json_encode(['success' => true, 'message' => 'Görüşme tamamlandı olarak işaretlendi.']);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Geçersiz işlem.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>
