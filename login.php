<?php
session_start();
require_once 'config/database.php';

$mesaj = ''; $mesaj_tur = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $giris_turu = $_POST['giris_turu'];
    
    // ========================================================
    // 🕵️‍♂️ GİZLİ YÖNETİCİ KAPISI (Kullanıcı Adı veya T.C. ile Giriş)
    // ========================================================
    if ($giris_turu == 'ogrenci') {
        $test_tc = trim($_POST['tc_kimlik']);
        $test_sifre = $_POST['sifre'];
        
        // Veritabanlı yönetici girişi: kullanici_adi VEYA tc_kimlik ile
        $stmt_admin = $db->prepare("SELECT * FROM users WHERE (kullanici_adi = ? OR tc_kimlik = ?) AND rol = 'yonetici' AND silindi_mi = 0");
        $stmt_admin->execute([$test_tc, $test_tc]);
        $kullanici_admin = $stmt_admin->fetch();

        if ($kullanici_admin && password_verify($test_sifre, $kullanici_admin['sifre'])) {
            $_SESSION['user_id'] = $kullanici_admin['id'];
            $_SESSION['ad_soyad'] = $kullanici_admin['adi'] . ' ' . $kullanici_admin['soyadi'];
            $_SESSION['rol'] = 'yonetici';
            
            echo "<!DOCTYPE html><html><body style='background:#000; color:#0f0; text-align:center; padding-top:20%; font-family:monospace; font-size:2rem;'>
                  <p>YETKİLİ ERİŞİMİ ONAYLANDI.</p>
                  <p>Yönetici Paneline Yönlendiriliyorsunuz...</p>
                  <script>setTimeout(function(){ window.location.href = 'pages/admin/dashboard.php'; }, 1500);</script>
                  </body></html>";
            exit;
        }
    }

    // 1. ÖĞRENCİ GİRİŞİ 
    if ($giris_turu == 'ogrenci') {
        $tc_kimlik = trim($_POST['tc_kimlik']);
        $sifre = $_POST['sifre'];

        $stmt = $db->prepare("SELECT * FROM ogrenciler WHERE tc_kimlik = ?");
        $stmt->execute([$tc_kimlik]);
        $kullanici = $stmt->fetch();

        if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
            $_SESSION['user_id'] = $kullanici['id'];
            $_SESSION['ad_soyad'] = $kullanici['adi'] . ' ' . $kullanici['soyadi'];
            $_SESSION['rol'] = 'ogrenci';
            $_SESSION['okul_no'] = $kullanici['okul_no'];
            header("Location: pages/student/dashboard.php"); exit;
        } else {
            $mesaj = "T.C. Kimlik numarası veya şifre hatalı!"; $mesaj_tur = "error";
        }
    } 
    // 2. VELİ GİRİŞİ (ÇOCUK SEÇİM EKRANINA GİDER)
    elseif ($giris_turu == 'veli') {
        $tc_kimlik = trim($_POST['tc_kimlik']);
        $sifre = $_POST['sifre'];

        $stmt = $db->prepare("SELECT * FROM users WHERE tc_kimlik = ? AND rol = 'veli' AND silindi_mi = 0");
        $stmt->execute([$tc_kimlik]);
        $kullanici = $stmt->fetch();

        if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
            $_SESSION['user_id'] = $kullanici['id'];
            $_SESSION['ad_soyad'] = $kullanici['adi'] . ' ' . $kullanici['soyadi'];
            $_SESSION['rol'] = 'veli';
            $_SESSION['telefon'] = $kullanici['telefon'];
            
            header("Location: pages/ogrenci_sec.php"); exit;
        } else {
            $mesaj = "T.C. Kimlik numarası veya şifre hatalı!"; $mesaj_tur = "error";
        }
    }
    // 3. ÖĞRETMEN GİRİŞİ
    elseif ($giris_turu == 'ogretmen') {
        $tc = trim($_POST['tc_kimlik']);
        $sifre = $_POST['sifre'];

        $stmt = $db->prepare("SELECT * FROM users WHERE (tc_kimlik = ? OR kullanici_adi = ?) AND rol = 'ogretmen' AND silindi_mi = 0");
        $stmt->execute([$tc, $tc]);
        $kullanici = $stmt->fetch();

        if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
            $_SESSION['user_id'] = $kullanici['id'];
            $_SESSION['ad_soyad'] = $kullanici['adi'] . ' ' . $kullanici['soyadi'];
            $_SESSION['rol'] = 'ogretmen';
            $_SESSION['brans'] = $kullanici['brans'];
            header("Location: pages/teacher/dashboard.php"); exit;
        } else {
            $mesaj = "T.C. Kimlik / Kullanıcı Adı veya şifre hatalı!"; $mesaj_tur = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sisteme Giriş Yap | Kurumsal Randevu Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/style.css?v=23">
    <style>
        .nav-pills .nav-link { color: #A0B2C6; border-radius: 30px; margin: 0 5px; transition: all 0.3s; }
        .nav-pills .nav-link.active { background-color: var(--neon-blue); color: var(--space-dark); font-weight: bold; box-shadow: 0 0 15px rgba(91, 192, 190, 0.5); }
        .glass-panel { background: rgba(28, 37, 65, 0.85); backdrop-filter: blur(15px); border: 1px solid rgba(91, 192, 190, 0.3); border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.5); padding: 40px; }
        .form-control { background: rgba(0,0,0,0.4) !important; color: #ffffff !important; border: 1px solid rgba(91,192,190,0.3) !important; border-radius: 10px; padding: 12px; }
        .form-control::placeholder { color: rgba(160,178,198,0.6) !important; }
        .form-control:focus { border-color: var(--neon-blue) !important; box-shadow: 0 0 10px rgba(91, 192, 190, 0.3) !important; background: rgba(0,0,0,0.6) !important; }
        .form-label { color: #A0B2C6; font-size: 0.85rem; font-weight: 500; }
    </style>
</head>
<body class="bg-dark-space d-flex align-items-center justify-content-center" style="height: 100vh;">

    <div id="particles-js" style="position: fixed; z-index: -1;"></div>

    <div class="container" style="max-width: 500px; z-index: 1;">
        <div class="text-center mb-4 slide-up-fade">
            <img src="assets/img/logo.png" alt="Logo" style="width: 110px; filter: drop-shadow(0 0 15px rgba(91,192,190,0.6)); mb-3">
            <h3 class="text-light fw-bold mt-3">Ahi Evran MTAL</h3>
            <p style="font-size: 0.95rem; color: #A0B2C6;">Sisteme giriş yapmak için profilinizi seçin</p>
        </div>

        <div class="glass-panel slide-up-fade" style="animation-delay: 0.2s;">
            
            <ul class="nav nav-pills justify-content-center mb-4" id="pills-tab" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#ogrenci" type="button"><i class="fa-solid fa-user-graduate me-2"></i>Öğrenci</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#veli" type="button"><i class="fa-solid fa-user-tie me-2"></i>Veli</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#ogretmen" type="button"><i class="fa-solid fa-chalkboard-user me-2"></i>Öğretmen</button></li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                
                <div class="tab-pane fade show active" id="ogrenci">
                    <form action="login.php" method="POST">
                        <input type="hidden" name="giris_turu" value="ogrenci">
                        <div class="mb-3">
                            <label class="form-label"><i class="fa-solid fa-id-card me-1 text-info"></i> T.C. Kimlik Numarası</label>
                            <input type="text" name="tc_kimlik" class="form-control" maxlength="11" placeholder="11 Haneli T.C. Kimlik" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label"><i class="fa-solid fa-lock me-1 text-info"></i> Şifre</label>
                            <input type="password" name="sifre" class="form-control" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-neon w-100 py-2 fw-bold"><i class="fa-solid fa-right-to-bracket me-2"></i> Öğrenci Girişi</button>
                    </form>
                </div>

                <div class="tab-pane fade" id="veli">
                    <form action="login.php" method="POST">
                        <input type="hidden" name="giris_turu" value="veli">
                        <div class="mb-3">
                            <label class="form-label"><i class="fa-solid fa-id-card me-1 text-warning"></i> T.C. Kimlik Numarası</label>
                            <input type="text" name="tc_kimlik" class="form-control" maxlength="11" placeholder="11 Haneli T.C. Kimliğinizi Girin" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label"><i class="fa-solid fa-lock me-1 text-warning"></i> Şifre</label>
                            <input type="password" name="sifre" class="form-control" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-neon w-100 py-2 fw-bold" style="border-color: #f6c23e; color: #f6c23e;"><i class="fa-solid fa-right-to-bracket me-2"></i> Veli Girişi</button>
                    </form>
                </div>

                <div class="tab-pane fade" id="ogretmen">
                    <form action="login.php" method="POST">
                        <input type="hidden" name="giris_turu" value="ogretmen">
                        <div class="mb-3">
                            <label class="form-label"><i class="fa-solid fa-id-card me-1 text-success"></i> T.C. Kimlik / Kullanıcı Adı</label>
                            <input type="text" name="tc_kimlik" class="form-control" maxlength="50" placeholder="T.C. Kimlik veya Kullanıcı Adınız" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label"><i class="fa-solid fa-lock me-1 text-success"></i> MEBBİS / Sistem Şifresi</label>
                            <input type="password" name="sifre" class="form-control" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-neon w-100 py-2 fw-bold" style="border-color: #28a745; color: #28a745;"><i class="fa-solid fa-right-to-bracket me-2"></i> Öğretmen Girişi</button>
                    </form>
                </div>
                
            </div>

            <div class="text-center mt-4 pt-3" style="border-top: 1px solid rgba(255,255,255,0.05);">
                <a href="register.php" class="text-decoration-none" style="font-size: 0.95rem; color: #E6F1F9;">Sistemde kaydınız yok mu? <span class="neon-text fw-bold">Hemen Kayıt Olun</span></a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="assets/js/app.js?v=23"></script>

    <?php if($mesaj != ''): ?>
    <script>
        Swal.fire({
            icon: '<?php echo $mesaj_tur; ?>',
            title: 'Hata!',
            text: '<?php echo $mesaj; ?>',
            background: '#1C2541', color: '#fff', confirmButtonColor: '#dc3545'
        });
    </script>
    <?php endif; ?>
</body>
</html>