<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Sadece öğretmen erişebilir
if ($_SESSION['rol'] !== 'ogretmen') {
    header("Location: ../dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$mesaj = ''; $mesaj_tur = ''; 

// Profil resmini ve güncel bilgileri çek
$stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND rol = 'ogretmen'");
$stmt->execute([$user_id]);
$aktif_kullanici = $stmt->fetch();

// 2FA Güncelleme
if (isset($_POST['toggle_2fa'])) {
    $yeni_durum = $aktif_kullanici['iki_adimli_dogrulama'] == 1 ? 0 : 1;
    $db->prepare("UPDATE users SET iki_adimli_dogrulama = ? WHERE id = ?")->execute([$yeni_durum, $user_id]);
    $mesaj = $yeni_durum ? "2FA Güvenliği Aktifleştirildi!" : "2FA Güvenliği Kapatıldı!";
    $mesaj_tur = $yeni_durum ? "success" : "info";
    header("Refresh: 1.5; url=profil.php");
}

// Profil Fotoğrafı Güncelleme
if (isset($_FILES['profil_foto']) && $_FILES['profil_foto']['error'] == 0) {
    $izin_verilenler = ['jpg', 'jpeg', 'png'];
    $dosya_uzantisi = strtolower(pathinfo($_FILES['profil_foto']['name'], PATHINFO_EXTENSION));
    if (in_array($dosya_uzantisi, $izin_verilenler)) {
        $yeni_isim = "teacher_" . $user_id . "_" . time() . "." . $dosya_uzantisi;
        $hedef_yol = "../../assets/img/" . $yeni_isim;
        if (move_uploaded_file($_FILES['profil_foto']['tmp_name'], $hedef_yol)) {
            $db->prepare("UPDATE users SET profil_resmi = ? WHERE id = ?")->execute([$yeni_isim, $user_id]);
            $mesaj = "Profil fotoğrafınız güncellendi!"; $mesaj_tur = "success";
            header("Refresh: 1; url=profil.php");
        }
    } else { $mesaj = "Sadece JPG ve PNG biçimleri kabul ediliyor!"; $mesaj_tur = "error"; }
}

if (isset($_POST['hazir_avatar'])) {
    $secilen_avatar = $_POST['hazir_avatar']; 
    $db->prepare("UPDATE users SET profil_resmi = ? WHERE id = ?")->execute([$secilen_avatar, $user_id]);
    header("Location: profil.php"); exit;
}

// Bilgi Güncelleme
if (isset($_POST['profil_guncelle'])) {
    $email = trim($_POST['email'] ?? ''); 
    $telefon = trim($_POST['telefon'] ?? ''); 
    
    $db->prepare("UPDATE users SET email=?, telefon=? WHERE id=?")->execute([$email, $telefon, $user_id]);
    $mesaj = "İletişim bilgileriniz başarıyla kaydedildi!"; $mesaj_tur = "success";
    header("Refresh: 1.5; url=profil.php");
}

// Şifre Güncelleme
if (isset($_POST['sifre_guncelle'])) {
    $eski_sifre = $_POST['eski_sifre'];
    $yeni_sifre = $_POST['yeni_sifre'];
    
    if (password_verify($eski_sifre, $aktif_kullanici['sifre'])) {
        $yeni_hash = password_hash($yeni_sifre, PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET sifre = ? WHERE id = ?")->execute([$yeni_hash, $user_id]);
        $mesaj = "Güvenlik parolanız güncellendi!"; $mesaj_tur = "success";
    } else {
        $mesaj = "Mevcut parolanızı yanlış girdiniz!"; $mesaj_tur = "error";
    }
}

// Güncel bilgileri tekrar çek
$stmt->execute([$user_id]);
$aktif_kullanici = $stmt->fetch();
$profil_resmi = $aktif_kullanici['profil_resmi'] ? $aktif_kullanici['profil_resmi'] : 'fa-chalkboard-user';

$resim_html = strpos($profil_resmi, 'fa-') === 0 
    ? "<i class='fa-solid $profil_resmi text-light' style='font-size: 3rem;'></i>" 
    : "<img src='../../assets/img/$profil_resmi' style='width: 100%; height: 100%; object-fit: cover;'>";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğretmen Profilim | Ahi Evran MTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../../assets/css/style.css?v=25">
    <style>
        .input-locked { background-color: rgba(255, 255, 255, 0.02) !important; color: #E6F1F9 !important; cursor: not-allowed; pointer-events: none; border-bottom: 2px solid rgba(255, 255, 255, 0.05) !important; }
        .file-upload-btn, .avatar-btn { position: absolute; bottom: 0px; background: var(--neon-blue); color: var(--space-dark); border-radius: 50%; width: 32px; height: 32px; display: flex; justify-content: center; align-items: center; cursor: pointer; border: 2px solid var(--card-dark); transition: transform 0.3s; z-index: 10; font-size: 0.85rem; }
        .file-upload-btn { right: 0px; } .avatar-btn { left: 0px; background: #f6c23e; }
        .file-upload-btn:hover, .avatar-btn:hover { transform: scale(1.1); }
        .compact-label { font-size: 0.9rem !important; margin-bottom: 2px !important; display: flex; justify-content: space-between; align-items: flex-end; color: #A0B2C6; }
        .compact-input { padding-top: 4px !important; padding-bottom: 4px !important; font-size: 0.95rem !important; }
        .glass-dropdown .dropdown-item:hover { background: rgba(91, 192, 190, 0.2) !important; color: #fff !important; }
        
        .security-card { background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.3); border-radius: 15px; padding: 20px; transition: 0.3s; }
    </style>
</head>
<body class="bg-dark-space">

    <div class="school-watermark"><i class="fa-solid fa-graduation-cap"></i></div>
    <div id="particles-js" style="position: fixed; z-index: -1;"></div>

    <!-- SİDEBAR -->
    <nav class="glass-sidebar">
        <div class="text-center mb-5 mt-3 px-2 text-light fw-bold" style="border-bottom: 1px solid rgba(91, 192, 190, 0.2); padding-bottom: 20px;">
            <img src="../../assets/img/logo.png" alt="Ahi Evran MTAL Logosu" class="sidebar-logo mb-3">
            <br>
            <span class="sidebar-text" style="font-size: 0.9rem; line-height: 1.5; display: block;">
                Ahi Evran Mesleki Ve Teknik<br>Anadolu Lisesi<br>
                <span class="neon-text" style="font-size: 0.75rem; font-weight: 400;">Öğretmen Eğitim Portalı</span>
            </span>
        </div>
        
        <!-- YENİ MENÜLER BURADA -->
        <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-chalkboard-user"></i> <span class="sidebar-text">Öğretmen Paneli</span></a>
        <a href="profil.php" class="sidebar-link active" style="background: rgba(91, 192, 190, 0.15); border-right: 4px solid var(--neon-blue);"><i class="fa-solid fa-address-card"></i> <span class="sidebar-text">Öğretmen Profilim</span></a>
        
        <div style="position: absolute; bottom: 20px; width: 100%;">
            <a href="../../logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-power-off text-danger"></i> <span class="sidebar-text">Oturumu Kapat</span></a>
        </div>
    </nav>

    <!-- ANA İÇERİK -->
    <div class="main-content" style="padding: 30px 50px;">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="text-light fw-bold m-0"><i class="fa-solid fa-user-gear text-info me-2"></i>Eğitmen Profil Ayarları</h4>
            <button class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 40px; height: 40px; border-color: rgba(255,255,255,0.2);" onclick="toggleThemeMode()" title="Gündüz/Gece Modu">
                <i id="theme-icon-indicator" class="fa-solid fa-moon text-light"></i>
            </button>
        </div>

        <div class="row g-4 slide-up-fade">
            <!-- Temel Bilgiler Kolonu -->
            <div class="col-lg-8">
                <div class="glass-card p-4 mb-4">
                    <form action="profil.php" method="POST" enctype="multipart/form-data" id="fotoForm">
                        <div class="d-flex align-items-center mb-4 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
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
                                <p class="text-muted small m-0" style="color:#A0B2C6; font-size: 0.95rem;"><b>Alan/Branş:</b> <?php echo htmlspecialchars($aktif_kullanici['brans']); ?></p>
                            </div>
                        </div>
                    </form>

                    <form action="profil.php" method="POST">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 custom-input-group">
                                <label class="compact-label">T.C. Kimlik / Giriş Adı <i class="fa-solid fa-lock text-muted ms-1" style="font-size:0.7rem;"></i></label>
                                <div class="input-wrapper"><i class="fa-solid fa-fingerprint input-icon" style="color:#6c757d; font-size:0.9rem;"></i>
                                    <input type="text" class="form-control antigravity-input compact-input input-locked" value="<?php echo htmlspecialchars($aktif_kullanici['kullanici_adi'] ?? $aktif_kullanici['tc_kimlik']); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6 custom-input-group">
                                <label class="compact-label">Personel E-Posta</label>
                                <div class="input-wrapper"><i class="fa-solid fa-envelope input-icon" style="color:#6c757d; font-size:0.9rem;"></i>
                                    <input type="email" name="email" class="form-control antigravity-input compact-input" placeholder="mail@ornek.com" value="<?php echo htmlspecialchars($aktif_kullanici['email'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6 custom-input-group mt-4">
                                <label class="compact-label">İletişim / WhatsApp Numarası</label>
                                <div class="input-wrapper"><i class="fa-solid fa-phone input-icon" style="color:#6c757d; font-size:0.9rem;"></i>
                                    <input type="text" name="telefon" class="form-control antigravity-input compact-input" placeholder="5XX XXX XX XX" value="<?php echo htmlspecialchars($aktif_kullanici['telefon'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6 custom-input-group mt-4">
                                <label class="compact-label">Kısa Biyografi (Veli Görür)</label>
                                <div class="input-wrapper"><i class="fa-solid fa-pen input-icon" style="color:#6c757d; font-size:0.9rem;"></i>
                                    <input type="text" class="form-control antigravity-input compact-input input-locked" value="Bilgi güncellemeleri müdürlükten." readonly>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end align-items-center mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.05);">
                            <button type="submit" name="profil_guncelle" class="btn btn-neon fw-bold py-2 px-4 shadow-sm">
                                <i class="fa-solid fa-save me-2"></i> İletişim Bilgilerimi Güncelle
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sağ Kolon (Güvenlik) -->
            <div class="col-lg-4" id="guvenlik">
                <div class="glass-card p-4 mb-4">
                    <h5 class="text-light fw-bold mb-3"><i class="fa-solid fa-shield-halved text-warning me-2"></i> Güvenlik Bölgesi</h5>
                    
                    <form action="profil.php" method="POST" class="mb-4 pb-4" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <p class="text-muted" style="font-size: 0.85rem;">İki adımlı doğrulama (2FA) ile öğretmen hesabınızı okul dışından gelen girişlere karşı daha güçlü koruyabilirsiniz.</p>
                        <div class="d-grid">
                            <button type="submit" name="toggle_2fa" class="btn <?php echo $aktif_kullanici['iki_adimli_dogrulama'] ? 'btn-success' : 'btn-outline-success'; ?> fw-bold">
                                <i class="fa-solid fa-key me-2"></i> <?php echo $aktif_kullanici['iki_adimli_dogrulama'] ? '2FA Şifrelemesi Aktif' : '2FA Korumasını Aç'; ?>
                            </button>
                        </div>
                    </form>

                    <h6 class="text-light fw-bold mb-3"><i class="fa-solid fa-lock text-danger me-2"></i> Sistem Parolasını Değiştir</h6>
                    <form action="profil.php" method="POST">
                        <div class="mb-3 custom-input-group">
                            <label class="compact-label" style="color: #ff6b6b;">Mevcut Şifreniz</label>
                            <input type="password" name="eski_sifre" class="form-control antigravity-input" required>
                        </div>
                        <div class="mb-4 custom-input-group">
                            <label class="compact-label text-info">Yeni Şifreniz</label>
                            <input type="password" name="yeni_sifre" class="form-control antigravity-input" required>
                        </div>
                        <button type="submit" name="sifre_guncelle" class="btn btn-danger w-100 fw-bold">Parolamı Yenile</button>
                    </form>

                    <div class="mt-4 pt-3 text-center" style="border-top: 1px solid rgba(255,255,255,0.05);">
                        <p style="font-size: 0.8rem; color: #A0B2C6;"><i class="fa-solid fa-circle-info text-info me-1"></i> Sisteme en son girdiğinizde başarılı kaydedildi. Tüm işlemleriniz loglanmaktadır.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="avatarForm" action="profil.php" method="POST" style="display:none;"><input type="hidden" name="hazir_avatar" id="hazir_avatar_input"></form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="../../assets/js/app.js?v=25"></script>
    <script>
        function secAvatar() {
            Swal.fire({
                title: '<span style="color:#5BC0BE; font-size:1.2rem;">Karakter Seçimi</span>',
                html: `<div class="d-flex justify-content-center gap-3 mt-3">
                        <button class="btn btn-outline-light" onclick="avatarKaydet('fa-chalkboard-user')"><i class="fa-solid fa-chalkboard-user fa-2x"></i></button>
                        <button class="btn btn-outline-light" onclick="avatarKaydet('fa-user-tie')"><i class="fa-solid fa-user-tie fa-2x"></i></button>
                        <button class="btn btn-outline-light" onclick="avatarKaydet('fa-book-open-reader')"><i class="fa-solid fa-book-open-reader fa-2x"></i></button>
                       </div>`,
                background: '#1C2541', showConfirmButton: false, showCloseButton: true
            });
        }
        function avatarKaydet(ikon) { document.getElementById('hazir_avatar_input').value = ikon; document.getElementById('avatarForm').submit(); }
    </script>

    <?php if($mesaj != ''): ?>
    <script> document.addEventListener('DOMContentLoaded', function() { Swal.fire({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, background: '#1C2541', color: '#E6F1F9', icon: '<?php echo $mesaj_tur; ?>', title: '<?php echo $mesaj; ?>' }); }); </script>
    <?php endif; ?>

</body>
</html>
