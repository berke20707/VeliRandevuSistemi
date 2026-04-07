<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$rol = $_SESSION['rol'] ?? 'veli';
$mesaj = ''; $mesaj_tur = ''; 
$kayip_otp_goster = false;

if ($rol === 'ogrenci') {
    $stmt = $db->prepare("SELECT id, adi, soyadi, tc_kimlik, telefon, sinif, okul_no, '' as email, '' as cinsiyet, '' as kan_grubu, '' as profil_resmi, 0 as iki_adimli_dogrulama, 0 as hesap_donduruldu FROM ogrenciler WHERE id = ?");
} else {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
}
$stmt->execute([$user_id]);
$aktif_kullanici = $stmt->fetch();

if (isset($_POST['veri_indir'])) {
    $dosya_adi = "Veli_Veri_Dokumu_" . date('Ymd_His') . ".json";
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $dosya_adi);
    unset($aktif_kullanici['sifre']); 
    echo json_encode($aktif_kullanici, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_POST['toggle_2fa'])) {
    $yeni_durum = $aktif_kullanici['iki_adimli_dogrulama'] == 1 ? 0 : 1;
    $db->prepare("UPDATE users SET iki_adimli_dogrulama = ? WHERE id = ?")->execute([$yeni_durum, $user_id]);
    $mesaj = $yeni_durum ? "2FA Güvenliği Aktifleştirildi!" : "2FA Güvenliği Kapatıldı!";
    $mesaj_tur = $yeni_durum ? "success" : "info";
    header("Refresh: 1.5; url=profil.php");
}

if (isset($_POST['hesap_dondur'])) {
    $db->prepare("UPDATE users SET hesap_donduruldu = 1 WHERE id = ?")->execute([$user_id]);
    session_unset(); session_destroy();
    echo "<script>alert('Hesabınız başarıyla donduruldu. Çıkış yapılıyor.'); window.location.href='../login.php';</script>";
    exit;
}

if (isset($_POST['hesap_sil'])) {
    $db->prepare("UPDATE users SET silindi_mi = 1, silinme_tarihi = NOW() WHERE id = ?")->execute([$user_id]);
    session_unset(); session_destroy();
    echo "<script>alert('Hesabınız KVKK mevzuatı gereği 5 yıl saklanmak üzere arşive alınmış ve erişime kapatılmıştır.'); window.location.href='../login.php';</script>";
    exit;
}

if (isset($_FILES['profil_foto']) && $_FILES['profil_foto']['error'] == 0) {
    $izin_verilenler = ['jpg', 'jpeg', 'png'];
    $dosya_uzantisi = strtolower(pathinfo($_FILES['profil_foto']['name'], PATHINFO_EXTENSION));
    if (in_array($dosya_uzantisi, $izin_verilenler)) {
        $yeni_isim = "profil_" . $user_id . "_" . time() . "." . $dosya_uzantisi;
        $hedef_yol = "../assets/img/" . $yeni_isim;
        if (move_uploaded_file($_FILES['profil_foto']['tmp_name'], $hedef_yol)) {
            $db->prepare("UPDATE users SET profil_resmi = ? WHERE id = ?")->execute([$yeni_isim, $user_id]);
            $mesaj = "Fotoğraf güncellendi!"; $mesaj_tur = "success";
            header("Refresh: 1; url=profil.php");
        }
    } else { $mesaj = "Sadece JPG ve PNG!"; $mesaj_tur = "error"; }
}

if (isset($_POST['hazir_avatar'])) {
    $secilen_avatar = $_POST['hazir_avatar']; 
    $db->prepare("UPDATE users SET profil_resmi = ? WHERE id = ?")->execute([$secilen_avatar, $user_id]);
    header("Location: profil.php"); exit;
}

if (isset($_POST['profil_guncelle'])) {
    $tc_kimlik = trim($_POST['tc_kimlik'] ?? ''); 
    $kan_grubu = $_POST['kan_grubu'] ?? '';
    $hatirlatici_zamani = $_POST['hatirlatici_zamani'] ?? '';

    if (empty($aktif_kullanici['tc_kimlik']) && !empty($tc_kimlik)) {
        $db->prepare("UPDATE users SET tc_kimlik = ? WHERE id = ?")->execute([$tc_kimlik, $user_id]);
    }
    
    $db->prepare("UPDATE users SET kan_grubu=?, hatirlatici_zamani=? WHERE id=?")->execute([$kan_grubu, $hatirlatici_zamani, $user_id]);
    $mesaj = "Tercihleriniz başarıyla kaydedildi!"; $mesaj_tur = "success";
    header("Refresh: 1.5; url=profil.php");
}

if (isset($_POST['acil_tel_islem'])) {
    $yeni_acil = trim($_POST['acil_yeni_deger']);
    $db->prepare("UPDATE users SET acil_telefon = ? WHERE id = ?")->execute([$yeni_acil, $user_id]);
    $mesaj = "Yakınınızın telefonu güncellendi!"; $mesaj_tur = "success";
    header("Refresh: 1.5; url=profil.php");
}

if (isset($_POST['kayip_erisim_talep'])) {
    $_SESSION['kayip_tur'] = $_POST['kayip_tur'];
    $_SESSION['kayip_yeni_deger'] = $_POST['kayip_yeni_deger'];
    $_SESSION['kayip_otp'] = rand(100000, 999999); 
    $kayip_otp_goster = true;
}

if (isset($_POST['kayip_otp_onayla'])) {
    if ($_POST['girilen_kayip_otp'] == $_SESSION['kayip_otp']) {
        $kolon = $_SESSION['kayip_tur'] === 'telefon' ? 'telefon' : 'email';
        $yeni_deger = $_SESSION['kayip_yeni_deger'];
        $db->prepare("UPDATE users SET $kolon = ? WHERE id = ?")->execute([$yeni_deger, $user_id]);
        $mesaj = "Bilgileriniz başarıyla değiştirildi!"; $mesaj_tur = "success";
        unset($_SESSION['kayip_otp']); unset($_SESSION['kayip_tur']); unset($_SESSION['kayip_yeni_deger']);
        header("Refresh: 1.5; url=profil.php");
    } else { $mesaj = "Doğrulama kodu hatalı!"; $mesaj_tur = "error"; }
}

$stmt->execute([$user_id]);
$aktif_kullanici = $stmt->fetch();
$profil_resmi = $aktif_kullanici['profil_resmi'] ? $aktif_kullanici['profil_resmi'] : 'fa-user';

$resim_html = strpos($profil_resmi, 'fa-') === 0 
    ? "<i class='fa-solid $profil_resmi fa-3x text-light'></i>" 
    : "<img src='../assets/img/$profil_resmi' style='width: 100%; height: 100%; object-fit: cover;'>";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim | Ahi Evran MTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/style.css?v=14">
    <style>
        .input-locked { background-color: rgba(255, 255, 255, 0.02) !important; color: #E6F1F9 !important; cursor: not-allowed; pointer-events: none; border-bottom: 2px solid rgba(255, 255, 255, 0.05) !important; }
        .swal2-input-custom { background: #0B132B !important; color: #E6F1F9 !important; border: 1px solid #5BC0BE !important; }
        .swal2-input-custom::placeholder { color: rgba(160, 178, 198, 0.5) !important; }
        .file-upload-btn, .avatar-btn { position: absolute; bottom: 0px; background: var(--neon-blue); color: var(--space-dark); border-radius: 50%; width: 32px; height: 32px; display: flex; justify-content: center; align-items: center; cursor: pointer; border: 2px solid var(--card-dark); transition: transform 0.3s; z-index: 10; font-size: 0.85rem; }
        .file-upload-btn { right: 0px; } .avatar-btn { left: 0px; background: #f6c23e; }
        .file-upload-btn:hover, .avatar-btn:hover { transform: scale(1.1); }
        .school-watermark { position: fixed; bottom: -50px; left: -50px; font-size: 35rem; color: rgba(255, 255, 255, 0.02); z-index: 0; animation: spin 80s linear infinite; pointer-events: none; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .chat-bot-fab { position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background: var(--neon-blue); border-radius: 50%; display: flex; justify-content: center; align-items: center; color: var(--space-dark); font-size: 1.5rem; cursor: pointer; box-shadow: 0 0 20px rgba(91, 192, 190, 0.5); z-index: 1050; transition: all 0.3s ease; animation: float 3s ease-in-out infinite; }
        .chat-bot-fab:hover { transform: scale(1.1); background: #fff; color: var(--neon-blue); }
        .compact-label { font-size: 0.9rem !important; margin-bottom: 2px !important; display: flex; justify-content: space-between; align-items: flex-end; color: #A0B2C6; }
        .compact-input { padding-top: 4px !important; padding-bottom: 4px !important; font-size: 0.95rem !important; }
    </style>
</head>
<body class="bg-dark-space">

    <div class="school-watermark"><i class="fa-solid fa-graduation-cap"></i></div>
    <div id="particles-js" style="position: fixed; z-index: -1;"></div>
    <div class="chat-bot-fab" title="Okul Asistanı" onclick="Swal.fire({icon:'info', title:'Asistan', text:'Okul asistanı sistemi yakında aktif edilecektir.', background:'#1C2541', color:'#E6F1F9'})"><i class="fa-solid fa-headset"></i></div>

    <nav class="glass-sidebar">
        <div class="text-center mb-5 mt-3 px-2 text-light fw-bold" style="border-bottom: 1px solid rgba(91, 192, 190, 0.2); padding-bottom: 20px;">
            <img src="../assets/img/logo.png" alt="Ahi Evran MTAL Logosu" class="sidebar-logo mb-3">
            <br>
            <span class="sidebar-text" style="font-size: 0.9rem; line-height: 1.5; display: block;">
                Ahi Evran Mesleki Ve Teknik<br>Anadolu Lisesi<br>
                <?php if($rol !== 'ogrenci'): ?>
                    <span class="neon-text" style="font-size: 0.75rem; font-weight: 400;">Veli Randevu Sistemi</span>
                <?php endif; ?>
            </span>
        </div>
        <?php if($rol === 'ogrenci'): ?>
            <a href="student/dashboard.php" class="sidebar-link"><i class="fa-solid fa-house"></i> <span class="sidebar-text">Ana Panel</span></a>
            <a href="student/okulumuz.php" class="sidebar-link"><i class="fa-solid fa-school-flag"></i> <span class="sidebar-text">Okulumuz Hakkında</span></a>
        <?php else: ?>
            <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-house"></i> <span class="sidebar-text">Ana Panel</span></a>
            <a href="randevu_al.php" class="sidebar-link"><i class="fa-solid fa-calendar-plus"></i> <span class="sidebar-text">Randevu Al</span></a>
        <?php endif; ?>
        <a href="ogretmenlerimiz.php" class="sidebar-link"><i class="fa-solid fa-users-viewfinder"></i> <span class="sidebar-text">Eğitim Kadrosu</span></a>
        <a href="profil.php" class="sidebar-link active" style="background: rgba(91, 192, 190, 0.15); border-right: 4px solid var(--neon-blue);"><i class="fa-solid fa-user-gear"></i> <span class="sidebar-text">Profilim</span></a>
        <div style="position: absolute; bottom: 20px; width: 100%;">
            <a href="../logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket text-danger"></i> <span class="sidebar-text">Güvenli Çıkış</span></a>
        </div>
    </nav>

    <div class="main-content d-flex flex-column justify-content-center align-items-center" style="height: 100vh; padding: 0; position: relative;">
        
        <div style="position: absolute; top: 30px; right: 40px; z-index: 999;">
            <button class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 40px; height: 40px; border-color: rgba(255,255,255,0.2); transition: all 0.3s; background: rgba(0,0,0,0.3);" onclick="toggleThemeMode()" title="Gündüz/Gece Modu">
                <i id="theme-icon-indicator" class="fa-solid fa-moon text-light"></i>
            </button>
        </div>

        <div class="col-xl-10 col-lg-11 w-100 px-4 slide-up-fade" style="animation-delay: 0.1s; max-width: 1100px;">
            <div class="glass-card p-4">
                
                <form action="profil.php" method="POST" enctype="multipart/form-data" id="fotoForm">
                    <div class="d-flex align-items-center mb-3 pb-2" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <div class="position-relative me-4">
                            <div class="rounded-circle d-flex justify-content-center align-items-center overflow-hidden" style="width: 80px; height: 80px; background: rgba(0,0,0,0.3); border: 2px solid var(--neon-blue);">
                                <?php echo $resim_html; ?>
                            </div>
                            <label for="fotoUpload" class="file-upload-btn" title="Fotoğraf Yükle"><i class="fa-solid fa-camera"></i></label>
                            <input type="file" id="fotoUpload" name="profil_foto" accept="image/png, image/jpeg" style="display: none;" onchange="document.getElementById('fotoForm').submit();">
                            <div class="avatar-btn" title="Hazır İkon Seç" onclick="secAvatar()"><i class="fa-solid fa-icons"></i></div>
                        </div>
                        <div>
                            <h3 class="text-light fw-bold m-0"><span class="neon-text"><?php echo htmlspecialchars($aktif_kullanici['adi']); ?></span> <?php echo htmlspecialchars($aktif_kullanici['soyadi']); ?></h3>
                            <p class="text-muted small m-0" style="color:#A0B2C6; font-size: 0.9rem;">Hesap bilgilerinizi ve tercihlerinizi yönetin.</p>
                        </div>
                    </div>
                </form>

                <form action="profil.php" method="POST">
                    <div class="row g-3 mb-2">
                        <div class="col-md-4 custom-input-group">
                            <label class="compact-label">Adınız <i class="fa-solid fa-lock text-muted ms-1" style="font-size:0.7rem;"></i></label>
                            <div class="input-wrapper"><i class="fa-solid fa-id-card input-icon" style="color: #6c757d; font-size:0.9rem;"></i>
                                <input type="text" class="form-control antigravity-input compact-input input-locked" value="<?php echo htmlspecialchars($aktif_kullanici['adi']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 custom-input-group">
                            <label class="compact-label">Soyadınız <i class="fa-solid fa-lock text-muted ms-1" style="font-size:0.7rem;"></i></label>
                            <div class="input-wrapper"><i class="fa-solid fa-id-card input-icon" style="color: #6c757d; font-size:0.9rem;"></i>
                                <input type="text" class="form-control antigravity-input compact-input input-locked" value="<?php echo htmlspecialchars($aktif_kullanici['soyadi']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 custom-input-group">
                            <label class="compact-label">T.C. Kimlik <?php echo !empty($aktif_kullanici['tc_kimlik']) ? '<i class="fa-solid fa-lock text-muted ms-1" style="font-size:0.7rem;"></i>' : ''; ?></label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-fingerprint input-icon" style="<?php echo !empty($aktif_kullanici['tc_kimlik']) ? 'color: #6c757d;' : ''; ?> font-size:0.9rem;"></i>
                                <input type="text" name="tc_kimlik" class="form-control antigravity-input compact-input <?php echo !empty($aktif_kullanici['tc_kimlik']) ? 'input-locked' : ''; ?>" value="<?php echo htmlspecialchars($aktif_kullanici['tc_kimlik'] ?? ''); ?>" <?php echo !empty($aktif_kullanici['tc_kimlik']) ? 'readonly' : 'required pattern="\d{11}"'; ?>>
                                <?php if(empty($aktif_kullanici['tc_kimlik'])): ?><div class="input-glow-line"></div><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-6 custom-input-group">
                            <label class="compact-label">
                                <span>E-Posta <i class="fa-solid fa-lock text-muted ms-1" style="font-size:0.7rem;"></i></span>
                                <a href="#" style="font-size: 0.75rem; color: var(--neon-blue); text-decoration: none; pointer-events: auto;" onclick="erisimYok('email', '<?php echo htmlspecialchars($aktif_kullanici['email']); ?>')">Erişim yok mu?</a>
                            </label>
                            <div class="input-wrapper"><i class="fa-solid fa-envelope input-icon" style="color:#6c757d; font-size:0.9rem;"></i>
                                <input type="email" name="email" class="form-control antigravity-input compact-input input-locked" value="<?php echo htmlspecialchars($aktif_kullanici['email']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 custom-input-group">
                            <label class="compact-label">
                                <span>Telefon <i class="fa-solid fa-lock text-muted ms-1" style="font-size:0.7rem;"></i></span>
                                <a href="#" style="font-size: 0.75rem; color: var(--neon-blue); text-decoration: none; pointer-events: auto;" onclick="erisimYok('telefon', '<?php echo htmlspecialchars($aktif_kullanici['telefon'] ?? 'Belirtilmedi'); ?>')">Erişim yok mu?</a>
                            </label>
                            <div class="input-wrapper"><i class="fa-solid fa-phone input-icon" style="color:#6c757d; font-size:0.9rem;"></i>
                                <input type="text" name="telefon" class="form-control antigravity-input compact-input input-locked" value="<?php echo htmlspecialchars($aktif_kullanici['telefon'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-<?php echo $rol === 'ogrenci' ? '4' : '3'; ?> custom-input-group">
                            <label class="compact-label">Cinsiyet <i class="fa-solid fa-lock text-muted ms-1" style="font-size:0.7rem;"></i></label>
                            <div class="input-wrapper"><i class="fa-solid fa-venus-mars input-icon" style="color: #6c757d; font-size:0.9rem;"></i>
                                <input type="text" class="form-control antigravity-input compact-input input-locked" value="<?php echo htmlspecialchars($aktif_kullanici['cinsiyet']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-<?php echo $rol === 'ogrenci' ? '4' : '3'; ?> custom-input-group">
                            <label class="compact-label">Kan Grubu</label>
                            <div class="input-wrapper"><i class="fa-solid fa-droplet input-icon" style="color:#d33; font-size:0.9rem;"></i>
                                <select name="kan_grubu" class="form-control antigravity-input compact-input">
                                    <option value="" <?php echo empty($aktif_kullanici['kan_grubu']) ? 'selected' : ''; ?>>Seçiniz...</option>
                                    <?php 
                                    $kanlar = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', '0+', '0-'];
                                    foreach($kanlar as $k) {
                                        $sel = ($aktif_kullanici['kan_grubu'] == $k) ? 'selected' : '';
                                        echo "<option value='$k' $sel>$k</option>";
                                    }
                                    ?>
                                </select>
                                <div class="input-glow-line"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-<?php echo $rol === 'ogrenci' ? '4' : '3'; ?> custom-input-group d-flex flex-column align-items-start">
                            <div class="d-flex justify-content-between w-100 align-items-end mb-1">
                                <label class="m-0" style="font-size: 0.9rem; color: #A0B2C6;">Acil Tel. <i class="fa-solid fa-lock text-muted ms-1" style="font-size:0.7rem;"></i></label>
                                <a href="#" style="font-size: 0.65rem; color: var(--neon-blue); text-decoration: none; pointer-events: auto;" onclick="acilTelDegistir('<?php echo htmlspecialchars($aktif_kullanici['acil_telefon'] ?? 'Belirtilmedi'); ?>')">Yakın No Değiştir</a>
                            </div>
                            <div class="input-wrapper w-100"><i class="fa-solid fa-truck-medical input-icon" style="color:#6c757d; font-size:0.9rem;"></i>
                                <input type="text" class="form-control antigravity-input compact-input input-locked" value="<?php echo htmlspecialchars($aktif_kullanici['acil_telefon'] ?? ''); ?>" placeholder="Belirtilmedi" readonly>
                            </div>
                        </div>

                        <?php if($rol !== 'ogrenci'): ?>
                        <div class="col-md-3 custom-input-group">
                            <label class="compact-label">Hatırlatıcı</label>
                            <div class="input-wrapper"><i class="fa-solid fa-bell input-icon" style="font-size:0.9rem;"></i>
                                <select name="hatirlatici_zamani" class="form-control antigravity-input compact-input">
                                    <option value="15_dk" <?php echo ($aktif_kullanici['hatirlatici_zamani'] == '15_dk') ? 'selected' : ''; ?>>15 Dk Önce</option>
                                    <option value="1_saat" <?php echo ($aktif_kullanici['hatirlatici_zamani'] == '1_saat') ? 'selected' : ''; ?>>1 Saat Önce</option>
                                    <option value="1_gun" <?php echo ($aktif_kullanici['hatirlatici_zamani'] == '1_gun' || empty($aktif_kullanici['hatirlatici_zamani'])) ? 'selected' : ''; ?>>1 Gün Önce</option>
                                </select>
                                <div class="input-glow-line"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-end align-items-center mt-2 pt-3" style="border-top: 1px solid rgba(255,255,255,0.05);">
                        <button type="submit" name="profil_guncelle" class="btn btn-neon fw-bold py-2 px-4" style="font-size: 1rem;">
                            <i class="fa-solid fa-shield-halved me-2"></i> Bilgileri Kaydet
                        </button>
                    </div>
                </form>

                <div class="d-flex justify-content-start gap-2 mt-3 flex-wrap">
                    <form action="profil.php" method="POST" class="m-0">
                        <button type="submit" name="veri_indir" class="btn btn-outline-info" style="font-size: 0.85rem;"><i class="fa-solid fa-download"></i> İndir</button>
                    </form>
                    <form action="profil.php" method="POST" class="m-0">
                        <button type="submit" name="toggle_2fa" class="btn <?php echo $aktif_kullanici['iki_adimli_dogrulama'] ? 'btn-success' : 'btn-outline-success'; ?>" style="font-size: 0.85rem;"><i class="fa-solid fa-key"></i> <?php echo $aktif_kullanici['iki_adimli_dogrulama'] ? '2FA Açık' : '2FA Kapalı'; ?></button>
                    </form>
                    <form action="profil.php" method="POST" id="dondurForm" class="m-0">
                        <input type="hidden" name="hesap_dondur" value="1">
                        <button type="button" class="btn btn-outline-warning" style="font-size: 0.85rem;" onclick="Swal.fire({title: 'Emin misiniz?', text: 'Hesabınız askıya alınacak!', icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet, Dondur'}).then((r)=>{if(r.isConfirmed) document.getElementById('dondurForm').submit();})"><i class="fa-solid fa-snowflake"></i> Dondur</button>
                    </form>
                    <form action="profil.php" method="POST" id="silForm" class="m-0">
                        <input type="hidden" name="hesap_sil" value="1">
                        <button type="button" class="btn btn-outline-danger" style="font-size: 0.85rem;" onclick="Swal.fire({title: 'KALICI OLARAK SİL?', text: 'Hesabınız KVKK kapsamında 5 yıl arşive alınıp erişime tamamen kapatılacaktır. Onaylıyor musunuz?', icon: 'error', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Evet, Kalıcı Olarak Sil'}).then((r)=>{if(r.isConfirmed) document.getElementById('silForm').submit();})"><i class="fa-solid fa-trash-can"></i> Hesabı Sil</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <form id="avatarForm" action="profil.php" method="POST" style="display:none;"><input type="hidden" name="hazir_avatar" id="hazir_avatar_input"></form>
    <form id="acilForm" action="profil.php" method="POST" style="display:none;"><input type="hidden" name="acil_tel_islem" value="1"><input type="hidden" name="acil_yeni_deger" id="acil_yeni_deger"></form>
    <form id="kayipForm" action="profil.php" method="POST" style="display:none;"><input type="hidden" name="kayip_erisim_talep" value="1"><input type="hidden" name="kayip_tur" id="kayip_tur"><input type="hidden" name="kayip_yeni_deger" id="kayip_yeni_deger"></form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="../assets/js/app.js?v=14"></script>

    <script>
        function secAvatar() {
            Swal.fire({
                title: '<span style="color:#5BC0BE; font-size:1.2rem;">Hazır İkon Seç</span>',
                html: `<div class="d-flex justify-content-center gap-3 mt-3"><button class="btn btn-outline-light" onclick="avatarKaydet('fa-user')"><i class="fa-solid fa-user fa-2x"></i></button><button class="btn btn-outline-light" onclick="avatarKaydet('fa-user-tie')"><i class="fa-solid fa-user-tie fa-2x"></i></button><button class="btn btn-outline-light" onclick="avatarKaydet('fa-user-nurse')"><i class="fa-solid fa-user-nurse fa-2x"></i></button><button class="btn btn-outline-light" onclick="avatarKaydet('fa-user-graduate')"><i class="fa-solid fa-user-graduate fa-2x"></i></button></div>`,
                background: '#1C2541', showConfirmButton: false, showCloseButton: true
            });
        }
        function avatarKaydet(ikon) { document.getElementById('hazir_avatar_input').value = ikon; document.getElementById('avatarForm').submit(); }

        function acilTelDegistir(mevcut) {
            Swal.fire({
                title: `<span style="color:#f6c23e; font-size:1.3rem;">Acil Durum Numarası</span>`,
                html: `<div class="text-start mt-3 p-3 rounded" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1);"><p style="color:#A0B2C6; font-size:0.9rem; margin-bottom:5px;">Mevcut Kayıtlı Bilgi:</p><p class="fw-bold m-0" style="color:#E6F1F9; font-size:1.1rem;">${mevcut}</p></div><p style="color:#A0B2C6; font-size:0.85rem; margin-top:15px; text-align:left;">Yakınınızın <b>yeni</b> telefon numarasını aşağıya girin. (Örn: 544 255 82 42)</p><input type="text" id="yeni_acil_input" class="form-control swal2-input-custom text-center fs-5 mt-3 p-3" placeholder="5XX XXX XX XX" maxlength="13">`,
                background: '#1C2541', showCancelButton: true, confirmButtonText: '<i class="fa-solid fa-save me-1"></i> Kaydet', cancelButtonText: 'İptal', confirmButtonColor: '#f6c23e', cancelButtonColor: 'transparent',
                didOpen: () => {
                    document.getElementById('yeni_acil_input').addEventListener('input', function (e) {
                        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
                        e.target.value = !x[2] ? x[1] : x[1] + ' ' + x[2] + (x[3] ? ' ' + x[3] : '') + (x[4] ? ' ' + x[4] : '');
                    });
                },
                preConfirm: () => {
                    const val = document.getElementById('yeni_acil_input').value;
                    if (!val || val.length < 13) { Swal.showValidationMessage('Lütfen geçerli bir numara girin!'); }
                    return val;
                }
            }).then((result) => {
                if (result.isConfirmed) { document.getElementById('acil_yeni_deger').value = result.value; document.getElementById('acilForm').submit(); }
            });
        }

        function erisimYok(tur, mevcut) {
            document.getElementById('kayip_tur').value = tur; 
            let baslik = tur === 'telefon' ? 'Telefon Numarasını' : 'E-Posta Adresini';
            let placeholder = tur === 'telefon' ? 'Örn: 5XX XXX XX XX' : 'yeni@mail.com';
            let maxLen = tur === 'telefon' ? 'maxlength="13"' : '';

            Swal.fire({
                title: `<span style="color:#5BC0BE; font-size:1.3rem;">${baslik} Değiştirmek Mi İstiyorsunuz?</span>`,
                html: `<div class="text-start mt-3 p-3 rounded" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1);"><p style="color:#A0B2C6; font-size:0.9rem; margin-bottom:5px;">Mevcut Kayıtlı Bilgi:</p><p class="fw-bold m-0" style="color:#E6F1F9; font-size:1.1rem;">${mevcut}</p></div><p style="color:#A0B2C6; font-size:0.85rem; margin-top:15px; text-align:left;">Lütfen kullanmak istediğiniz <b>yeni</b> bilginizi aşağıya girin. Oraya bir kurtarma kodu göndereceğiz.</p><input type="text" id="kayip_input" class="form-control swal2-input-custom mt-3 p-3 text-center fs-5" placeholder="${placeholder}" ${maxLen} style="border-radius:10px;">`,
                background: '#1C2541', showCancelButton: true, confirmButtonText: '<i class="fa-solid fa-paper-plane me-1"></i> Kod Gönder', cancelButtonText: 'İptal', confirmButtonColor: '#5BC0BE', cancelButtonColor: 'transparent',
                didOpen: () => {
                    if(tur === 'telefon') {
                        document.getElementById('kayip_input').addEventListener('input', function (e) {
                            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
                            e.target.value = !x[2] ? x[1] : x[1] + ' ' + x[2] + (x[3] ? ' ' + x[3] : '') + (x[4] ? ' ' + x[4] : '');
                        });
                    }
                },
                preConfirm: () => {
                    const val = document.getElementById('kayip_input').value;
                    if (!val) { Swal.showValidationMessage('Lütfen yeni bilginizi girin!'); }
                    return val;
                }
            }).then((result) => {
                if (result.isConfirmed) { document.getElementById('kayip_yeni_deger').value = result.value; document.getElementById('kayipForm').submit(); }
            });
        }
    </script>

    <?php if($mesaj != '' && !$kayip_otp_goster): ?>
    <script> document.addEventListener('DOMContentLoaded', function() { Swal.fire({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, background: '#1C2541', color: '#E6F1F9', icon: '<?php echo $mesaj_tur; ?>', title: '<?php echo $mesaj; ?>' }); }); </script>
    <?php endif; ?>

    <?php if($kayip_otp_goster): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '<span style="color:#5BC0BE;">Kurtarma Kodu Gönderildi!</span>',
                html: `<p style="color:#A0B2C6; font-size:0.9rem;">Girdiğiniz <b><?php echo htmlspecialchars($_SESSION['kayip_yeni_deger']); ?></b> adresine/numarasına 6 haneli kurtarma kodu gönderildi.</p><p style="color:#f6c23e; font-size:0.8rem;">(Test Kodu: <b><?php echo $_SESSION['kayip_otp']; ?></b>)</p><form id="kayipOtpForm" action="profil.php" method="POST"><input type="text" name="girilen_kayip_otp" class="form-control swal2-input-custom text-center fs-4 fw-bold mb-3" placeholder="------" maxlength="6" required><input type="hidden" name="kayip_otp_onayla" value="1"></form>`,
                background: '#1C2541', showCancelButton: true, confirmButtonText: 'Kurtar ve Değiştir', cancelButtonText: 'İptal', confirmButtonColor: '#28a745', cancelButtonColor: '#d33',
                preConfirm: () => { document.getElementById('kayipOtpForm').submit(); }
            });
        });
    </script>
    <?php endif; ?>

</body>
</html>