<?php
// RANDEVU İPTAL API'SI
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

$veli_id = $_SESSION['user_id'];

try {
    $stmt = $db->prepare("DELETE FROM randevular WHERE veli_id = ? AND durum IN ('bekliyor', 'onaylandi')");
    $stmt->execute([$veli_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Randevunuz başarıyla iptal edildi ve sistemden silindi.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'İptal edilecek aktif randevu bulunamadı.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>
