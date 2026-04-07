<?php
// RANDEVU KAYDETME API'SI
// Bu dosya AJAX ile çağrılır ve randevu bilgilerini veritabanına kaydeder

header('Content-Type: application/json; charset=utf-8');

session_start();
require_once '../config/database.php';

// Güvenlik: Giriş yapılmış mı?
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum bulunamadı. Lütfen tekrar giriş yapın.']);
    exit;
}

// Sadece POST kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit;
}

// JSON verisini al
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri formatı.']);
    exit;
}

$veli_id = $_SESSION['user_id'];
$veli_ad_soyad = $_SESSION['ad_soyad'] ?? '';
$ogrenci_ad = $_SESSION['aktif_ogrenci_ad'] ?? '';
$ogrenci_sinif = $_SESSION['aktif_ogrenci_sinif'] ?? '';

$ogretmen_ad = trim($input['ogretmen_ad'] ?? '');
$tarih = trim($input['tarih'] ?? '');
$saat = trim($input['saat'] ?? '');
$gundem = trim($input['gundem'] ?? '');

// Validasyon
if (empty($ogretmen_ad) || empty($tarih) || empty($saat)) {
    echo json_encode(['success' => false, 'message' => 'Öğretmen, tarih ve saat zorunludur.']);
    exit;
}

// Tarih formatı kontrolü (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih)) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz tarih formatı.']);
    exit;
}

// Geçmiş tarih VEYA BUGÜN kontrolü
$tarihObj = new DateTime($tarih);
$bugun = new DateTime();
$bugun->setTime(0, 0, 0);
if ($tarihObj <= $bugun) {
    echo json_encode(['success' => false, 'message' => 'Bugüne veya geçmiş bir tarihe randevu alınamaz. Lütfen yarın veya daha ileri bir tarih seçin.']);
    exit;
}

// Aynı velinin aktif randevusu var mı kontrolü
$kontrol_stmt = $db->prepare("SELECT COUNT(*) as sayi FROM randevular WHERE veli_id = ? AND durum IN ('bekliyor', 'onaylandi')");
$kontrol_stmt->execute([$veli_id]);
$aktif_sayi = $kontrol_stmt->fetch()['sayi'];

if ($aktif_sayi > 0) {
    echo json_encode(['success' => false, 'message' => 'Zaten aktif bir randevunuz bulunmaktadır. Önce mevcut randevunuzu iptal edin.']);
    exit;
}

// Aynı öğretmen, tarih ve saatte başka bir randevu var mı kontrolü
$cakisma_stmt = $db->prepare("SELECT COUNT(*) as sayi FROM randevular WHERE ogretmen_ad = ? AND tarih = ? AND saat = ? AND durum IN ('bekliyor', 'onaylandi')");
$cakisma_stmt->execute([$ogretmen_ad, $tarih, $saat]);
$cakisma_sayi = $cakisma_stmt->fetch()['sayi'];

if ($cakisma_sayi > 0) {
    echo json_encode(['success' => false, 'message' => 'Bu öğretmen için seçtiğiniz tarih ve saatte zaten bir randevu bulunmaktadır.']);
    exit;
}

try {
    // Randevuyu veritabanına kaydet
    $stmt = $db->prepare("INSERT INTO randevular (veli_id, veli_ad_soyad, ogrenci_ad, ogrenci_sinif, ogretmen_ad, tarih, saat, gundem, durum) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'bekliyor')");
    $stmt->execute([
        $veli_id,
        $veli_ad_soyad,
        $ogrenci_ad,
        $ogrenci_sinif,
        $ogretmen_ad,
        $tarih,
        $saat,
        $gundem
    ]);

    $randevu_id = $db->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Randevunuz başarıyla oluşturuldu!',
        'randevu' => [
            'id' => $randevu_id,
            'veli_ad_soyad' => $veli_ad_soyad,
            'ogrenci_ad' => $ogrenci_ad,
            'ogrenci_sinif' => $ogrenci_sinif,
            'ogretmen_ad' => $ogretmen_ad,
            'tarih' => $tarih,
            'saat' => $saat,
            'gundem' => $gundem,
            'durum' => 'bekliyor'
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>
