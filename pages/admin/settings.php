<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

if ($_SESSION['rol'] !== 'yonetici') { header("Location: ../dashboard.php"); exit; }

$ad_soyad = $_SESSION['ad_soyad'];
$mesaj = "";

// Yönetici profilini al
$mudur_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$mudur_stmt->execute([$_SESSION['user_id']]);
$mudur = $mudur_stmt->fetch();

// Fonksiyonlar
function update_setting($db, $key, $val) {
    $c = $db->query("SELECT id FROM settings WHERE setting_key='$key'")->fetchColumn();
    if($c) { $db->prepare("UPDATE settings SET setting_value=? WHERE setting_key=?")->execute([$val, $key]); }
    else { $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)")->execute([$key, $val]); }
}

// 1. Profil Güncelleme (Müdür)
if (isset($_POST['profil_guncelle'])) {
    $y_adi = trim($_POST['m_adi']);
    $y_soyadi = trim($_POST['m_soyadi']);
    $y_kullanici = trim($_POST['m_kullanici_adi']);
    $y_sifre = trim($_POST['m_sifre']);
    
    // Aynı kullanıcı adını kullanan başkası var mı?
    $check = $db->prepare("SELECT id FROM users WHERE kullanici_adi = ? AND id != ?");
    $check->execute([$y_kullanici, $_SESSION['user_id']]);
    if ($check->fetch()) {
        $mesaj = "<div class='alert alert-danger'>Bu kullanıcı adı zaten başka biri tarafından kullanılıyor!</div>";
    } else {
        if (!empty($y_sifre)) {
            // Şifre de değişecek
            $hash = password_hash($y_sifre, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET adi=?, soyadi=?, kullanici_adi=?, sifre=? WHERE id=?");
            $stmt->execute([$y_adi, $y_soyadi, $y_kullanici, $hash, $_SESSION['user_id']]);
        } else {
            // Sadece diğer bilgiler
            $stmt = $db->prepare("UPDATE users SET adi=?, soyadi=?, kullanici_adi=? WHERE id=?");
            $stmt->execute([$y_adi, $y_soyadi, $y_kullanici, $_SESSION['user_id']]);
        }
        
        $_SESSION['ad_soyad'] = $y_adi . ' ' . $y_soyadi;
        $ad_soyad = $_SESSION['ad_soyad'];
        
        // Tekrar bilgileri çekelim güncel halini formda göstermek için
        $mudur_stmt->execute([$_SESSION['user_id']]);
        $mudur = $mudur_stmt->fetch();
        
        $mesaj = "<div class='alert alert-success'>Profiliniz başarıyla güncellendi! Yönetim paneli artık yeni bilgilerinizle çalışacak.</div>";
    }
}

// 2. Ayar Güncellemeleri
// 2. Ayar Güncellemeleri
if (isset($_POST['kaydet'])) {
    if(isset($_POST['saatler'])) { update_setting($db, 'appointment_hours', trim($_POST['saatler'])); }
    if(isset($_POST['tatiller'])) { update_setting($db, 'holidays', trim($_POST['tatiller'])); }
    $mesaj = "<div class='alert alert-success'>Sistem ayarları başarıyla güncellendi. Takvim ve Randevu Sistemi yeni ayarlarla çalışacak.</div>";
}

// 2.5 Ders Programı Yükleme (PDF/Resim)
if (isset($_POST['ders_program_yukle'])) {
    if(isset($_FILES['program_dosya']) && $_FILES['program_dosya']['error'] == 0) {
        $dosya_adi = time() . '_' . basename($_FILES['program_dosya']['name']);
        $hedef_klasor = '../../uploads/';
        if(!is_dir($hedef_klasor)) { mkdir($hedef_klasor, 0777, true); }
        $hedef_yol = $hedef_klasor . $dosya_adi;
        
        $ext = strtolower(pathinfo($hedef_yol, PATHINFO_EXTENSION));
        if(in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'])) {
            if(move_uploaded_file($_FILES['program_dosya']['tmp_name'], $hedef_yol)) {
                update_setting($db, 'ders_programi_url', 'uploads/' . $dosya_adi);
                $mesaj = "<div class='alert alert-success'><i class='fa-solid fa-check me-2'></i>Genel ders programı başarıyla sisteme yüklendi ve yayınlandı.</div>";
            }
        } else {
            $mesaj = "<div class='alert alert-danger'>Sadece PDF, JPG veya PNG formatlarında dosya yükleyebilirsiniz.</div>";
        }
    }
}

// 3. Branş Ekle
if (isset($_POST['brans_ekle'])) {
    $b_adi = trim($_POST['brans_adi']);
    $c = $db->query("SELECT id FROM branches WHERE name='$b_adi'")->fetchColumn();
    if(!$c && !empty($b_adi)) {
        $db->prepare("INSERT INTO branches (name) VALUES (?)")->execute([$b_adi]);
        $mesaj = "<div class='alert alert-success'>Yeni alan/bölüm kaydedildi.</div>";
    }
}

// 4. Branş Sil
if (isset($_GET['brans_sil'])) {
    $db->prepare("DELETE FROM branches WHERE id=?")->execute([(int)$_GET['brans_sil']]);
    header("Location: settings.php"); exit;
}

// Mevcut değerleri çek
function get_setting($db, $key, $def="") {
    $r = $db->query("SELECT setting_value FROM settings WHERE setting_key='$key'")->fetchColumn();
    return $r ? $r : $def;
}

$mevcut_saatler = get_setting($db, 'appointment_hours', "09:00, 09:50, 10:40, 11:30, 12:20, 13:50, 14:40");
$mevcut_tatiller = get_setting($db, 'holidays', "2026-01-01, 2026-04-23, 2026-05-19, 2026-10-29");
$mevcut_program = get_setting($db, 'ders_programi_url', "");
$branslar = $db->query("SELECT * FROM branches ORDER BY name")->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sistem Ayarları & Profil | Ahi Evran MTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .custom-setting-input {
            width: 100% !important;
            background: rgba(0,0,0,0.3) !important;
            border: 1px solid rgba(91,192,190,0.3) !important;
            color: var(--text-light) !important;
            padding: 12px 15px;
            border-radius: 10px;
            box-shadow: none !important;
            transition: all 0.3s ease;
        }
        .custom-setting-input:focus {
            border-color: var(--neon-blue) !important;
            background: rgba(0,0,0,0.5) !important;
            box-shadow: 0 0 15px rgba(91,192,190,0.3) !important;
        }
        .profile-label { color: #A0B2C6; font-size: 0.85rem; font-weight: 500; margin-bottom: 5px; }
    </style>
</head>
<body class="bg-dark-space">
    <div class="school-watermark"><i class="fa-solid fa-graduation-cap"></i></div>
    <div id="particles-js" style="position: fixed; z-index: -1;"></div>

    <nav class="glass-sidebar">
        <div class="text-center mb-5 mt-3 px-2 text-light fw-bold" style="border-bottom: 1px solid rgba(91, 192, 190, 0.2); padding-bottom: 20px;">
            <img src="../../assets/img/logo.png" alt="Logo" class="sidebar-logo mb-3"><br>
            <span class="sidebar-text" style="font-size: 0.9rem;">Ahi Evran MTAL<br><span class="neon-text" style="font-size: 0.75rem;">Yönetim Paneli</span></span>
        </div>
        <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-gauge-high"></i> <span class="sidebar-text">Yönetim Paneli</span></a>
        <a href="teachers.php" class="sidebar-link"><i class="fa-solid fa-chalkboard-user"></i> <span class="sidebar-text">Eğitim Kadrosu</span></a>
        <a href="users.php" class="sidebar-link"><i class="fa-solid fa-users"></i> <span class="sidebar-text">Veli & Öğrenci VT</span></a>
        <a href="appointments.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> <span class="sidebar-text">Tüm Randevular</span></a>
        <a href="announcements.php" class="sidebar-link"><i class="fa-solid fa-bullhorn"></i> <span class="sidebar-text">Duyuru Yönetimi</span></a>
        <a href="blacklist.php" class="sidebar-link"><i class="fa-solid fa-user-xmark text-danger"></i> <span class="sidebar-text">Kara Liste</span></a>
        <a href="settings.php" class="sidebar-link active" style="background: rgba(91, 192, 190, 0.15); border-right: 4px solid var(--neon-blue);"><i class="fa-solid fa-gears"></i> <span class="sidebar-text">Sistem Ayarları</span></a>
        <div style="position: absolute; bottom: 20px; width: 100%;">
            <a href="../../logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket text-danger"></i> <span class="sidebar-text">Güvenli Çıkış</span></a>
        </div>
    </nav>

    <div class="main-content" style="padding: 30px 50px;">
        <h4 class="text-light fw-bold mb-4"><i class="fa-solid fa-gears text-info me-2"></i>Sistem & Profil Ayarları</h4>
        <?php echo $mesaj; ?>

        <div class="row g-4 mb-4" id="profil">
            <!-- Müdür Profil Güncelleme Alanı -->
            <div class="col-12">
                <div class="glass-card p-4 border border-info">
                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded-circle bg-dark d-flex justify-content-center align-items-center me-3 border border-info shadow" style="width: 60px; height: 60px;">
                            <i class="fa-solid fa-user-shield text-info fs-3"></i>
                        </div>
                        <div>
                            <h5 class="text-light mb-0 text-info fw-bold">Yönetici Profilim</h5>
                            <small class="text-muted">Görünüm, giriş bilgileriniz ve şifrenizi değiştirin.</small>
                        </div>
                    </div>
                    
                    <form action="settings.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="profile-label"><i class="fa-solid fa-signature text-info me-1"></i> Adınız</label>
                                <input type="text" name="m_adi" class="custom-setting-input" value="<?php echo htmlspecialchars($mudur['adi'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="profile-label"><i class="fa-solid fa-signature text-info me-1"></i> Soyadınız</label>
                                <input type="text" name="m_soyadi" class="custom-setting-input" value="<?php echo htmlspecialchars($mudur['soyadi'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="profile-label"><i class="fa-solid fa-user-lock text-warning me-1"></i> Sisteme Giriş Adı (Kullanıcı Adı)</label>
                                <input type="text" name="m_kullanici_adi" class="custom-setting-input" value="<?php echo htmlspecialchars($mudur['kullanici_adi'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="profile-label"><i class="fa-solid fa-key text-danger me-1"></i> Yeni Şifre (Değiştirmeyecekseniz Boş Bırakın)</label>
                                <input type="password" name="m_sifre" class="custom-setting-input" placeholder="••••••••">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" name="profil_guncelle" class="btn btn-info fw-bold px-4 text-dark"><i class="fa-solid fa-user-check"></i> Profilimi Güncelle</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Temel Ayarlar -->
            <div class="col-lg-7">
                <div class="glass-card p-4 h-100">
                    <h5 class="text-light mb-4 text-warning"><i class="fa-solid fa-clock"></i> Zaman ve Takvim Parametreleri</h5>
                    <form action="settings.php" method="POST">
                        <div class="mb-4">
                            <label class="form-label text-light fw-bold">Okul Zil/Randevu Saatleri</label>
                            <p class="text-light" style="font-size: 0.8rem;">Velilerin takvimden randevu alabilecekleri sabit dilimleri virgülle ayırarak belirleyin.</p>
                            <input type="text" name="saatler" class="custom-setting-input" value="<?php echo htmlspecialchars($mevcut_saatler); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-light fw-bold">Resmi / Özel Tatil Günleri (YYYY-MM-DD)</label>
                            <p class="text-light" style="font-size: 0.8rem;">Bu günlerde randevu alımı sistem tarafından otomatik kapatılacaktır (Takvimde kırmızı görünür).</p>
                            <textarea name="tatiller" class="custom-setting-input" rows="3"><?php echo htmlspecialchars($mevcut_tatiller); ?></textarea>
                        </div>
                        <div class="d-flex justify-content-end mb-4">
                            <button type="submit" name="kaydet" class="btn btn-warning fw-bold px-4"><i class="fa-solid fa-save"></i> Takvim Ayarlarını Kaydet</button>
                        </div>
                    </form>
                    
                    <h5 class="text-light mb-4 text-info mt-3" style="border-top:1px solid rgba(255,255,255,0.1); padding-top:20px;"><i class="fa-solid fa-calendar-days"></i> Genel Ders Programı Yayınla</h5>
                    <form action="settings.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="profile-label text-light">E-Okul Ders Programı Çıktısı <small class="text-warning">(*.pdf, *.jpg, *.png)</small></label>
                            <input type="file" name="program_dosya" class="custom-setting-input bg-dark text-light" accept=".pdf, image/*" required>
                            
                            <?php if(!empty($mevcut_program)): ?>
                            <div class="mt-3 p-2 rounded" style="background: rgba(13,202,240,0.1); border: 1px solid #0dcaf0;">
                                <i class="fa-solid fa-circle-check text-info me-1"></i> Şu an yayında aktif bir program mevcut. Yenisini yüklerseniz eskisinin üzerine yazılır.<br>
                                <a href="../../<?php echo htmlspecialchars($mevcut_program); ?>" target="_blank" class="btn btn-sm btn-outline-info mt-2"><i class="fa-solid fa-eye"></i> Mevcut Programı İncele</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" name="ders_program_yukle" class="btn btn-info text-dark fw-bold px-4"><i class="fa-solid fa-upload"></i> Sisteme Yansıt</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Branş/Bölüm Yönetimi -->
            <div class="col-lg-5">
                <div class="glass-card p-4 h-100">
                    <h5 class="text-light mb-4 text-primary"><i class="fa-solid fa-layer-group"></i> Branş & Meslek Alan Yönetimi</h5>
                    
                    <form action="settings.php" method="POST" class="d-flex gap-2 mb-4">
                        <input type="text" name="brans_adi" class="custom-setting-input" placeholder="Örn: Bilişim Teknolojileri" required>
                        <button type="submit" name="brans_ekle" class="btn btn-primary fw-bold text-nowrap"><i class="fa-solid fa-plus"></i> Ekle</button>
                    </form>

                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach($branslar as $b): ?>
                            <div class="badge bg-secondary p-2 ps-3 d-flex align-items-center" style="font-size: 0.85rem; border:1px solid rgba(255,255,255,0.1);">
                                <?php echo htmlspecialchars($b['name']); ?>
                                <a href="?brans_sil=<?php echo $b['id']; ?>" class="text-danger ms-2" title="Sil" onclick="return confirm('Silinsin mi?');"><i class="fa-solid fa-times-circle"></i></a>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if(count($branslar) == 0): ?>
                            <div class="text-muted w-100 text-center py-3">Hiç kayıtlı alan yok.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="../../assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
