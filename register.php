<?php
session_start();
require_once 'config/database.php';

$mesaj = ''; $mesaj_tur = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kayit_turu = $_POST['kayit_turu'];
    
    // 1. ÖĞRENCİ KAYDI
    if ($kayit_turu == 'ogrenci') {
        $ad = trim($_POST['adi']); 
        $soyad = trim($_POST['soyadi']);
        $sinif = trim($_POST['sinif']); 
        $okul_no = trim($_POST['okul_no']);
        $tc = trim($_POST['tc_kimlik']); 
        // İSİMLER DEĞİŞTİ! APP.JS ARTIK BULAMAYACAK!
        $ogr_tel = trim($_POST['ogrenci_telefon_input'] ?? '');
        $veli_adi = trim($_POST['veli_adi']); 
        $veli_tel = trim($_POST['veli_telefon_input']);
        $sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT);

        try {
            $stmt = $db->prepare("INSERT INTO ogrenciler (adi, soyadi, sinif, okul_no, tc_kimlik, telefon, veli_adi, veli_telefon, sifre) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$ad, $soyad, $sinif, $okul_no, $tc, $ogr_tel, $veli_adi, $veli_tel, $sifre]);
            $mesaj = "Öğrenci kaydınız başarıyla oluşturuldu! T.C. Kimlik numaranızla giriş yapabilirsiniz."; $mesaj_tur = "success";
        } catch(PDOException $e) {
            $mesaj = "Hata: Bu TC veya Okul No zaten kayıtlı!"; $mesaj_tur = "error";
        }
    } 
    // 2. VELİ KAYDI
    elseif ($kayit_turu == 'veli') {
        $ad = trim($_POST['adi']); 
        $soyad = trim($_POST['soyadi']);
        $tc = trim($_POST['tc_kimlik']); 
        $email = trim($_POST['email']);
        // İSİMLER DEĞİŞTİ! APP.JS ARTIK BULAMAYACAK!
        $telefon = trim($_POST['veli_kendi_telefon_input']); 
        $sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT);

        try {
            $stmt = $db->prepare("INSERT INTO users (adi, soyadi, tc_kimlik, email, telefon, sifre, rol) VALUES (?,?,?,?,?,?,'veli')");
            $stmt->execute([$ad, $soyad, $tc, $email, $telefon, $sifre]);
            $mesaj = "Veli kaydınız başarıyla oluşturuldu! T.C. Kimlik numaranızla giriş yapabilirsiniz."; $mesaj_tur = "success";
        } catch(PDOException $e) {
            $mesaj = "Hata: Bu TC, Email veya Telefon zaten kayıtlı!"; $mesaj_tur = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sisteme Kayıt Ol | Kurumsal Randevu Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/style.css?v=99">
    <style>
        .nav-pills .nav-link { color: #A0B2C6; border-radius: 30px; margin: 0 5px; transition: all 0.3s; }
        .nav-pills .nav-link.active { background-color: var(--neon-blue); color: var(--space-dark); font-weight: bold; box-shadow: 0 0 15px rgba(91, 192, 190, 0.5); }
        .glass-panel { background: rgba(28, 37, 65, 0.85); backdrop-filter: blur(15px); border: 1px solid rgba(91, 192, 190, 0.3); border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.5); padding: 40px; }
        .form-control { background: rgba(0,0,0,0.4) !important; color: #ffffff !important; border: 1px solid rgba(91,192,190,0.3) !important; border-radius: 10px; }
        .form-control::placeholder { color: rgba(160, 178, 198, 0.6) !important; }
        .form-control:focus { border-color: var(--neon-blue) !important; box-shadow: 0 0 10px rgba(91, 192, 190, 0.3) !important; background: rgba(0,0,0,0.6) !important; }
        .form-label { color: #A0B2C6; font-size: 0.85rem; font-weight: 500; }
        .zorunlu { color: #dc3545; font-weight: bold; margin-left: 3px; font-size: 1rem; } 
    </style>
</head>
<body class="bg-dark-space d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 20px 0;">

    <div id="particles-js" style="position: fixed; z-index: -1;"></div>

    <div class="container" style="max-width: 800px; z-index: 1;">
        <div class="text-center mb-4 slide-up-fade">
            <img src="assets/img/logo.png" alt="Logo" style="width: 100px; filter: drop-shadow(0 0 10px rgba(91,192,190,0.5)); mb-3">
            <h2 class="text-light fw-bold mt-3">Ahi Evran MTAL</h2>
            <p class="neon-text">Yeni Nesil Kayıt Portalı</p>
        </div>

        <div class="glass-panel slide-up-fade" style="animation-delay: 0.2s;">
            
            <ul class="nav nav-pills justify-content-center mb-4" id="pills-tab" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#ogrenci" type="button"><i class="fa-solid fa-user-graduate me-2"></i>Öğrenci</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#veli" type="button"><i class="fa-solid fa-user-tie me-2"></i>Veli</button></li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                
                <div class="tab-pane fade show active" id="ogrenci">
                    <form action="register.php" method="POST">
                        <input type="hidden" name="kayit_turu" value="ogrenci">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Öğrenci Adı <span class="zorunlu">*</span></label><input type="text" name="adi" class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label">Öğrenci Soyadı <span class="zorunlu">*</span></label><input type="text" name="soyadi" class="form-control" required></div>
                            <div class="col-md-4"><label class="form-label">Sınıfı (Örn: 10/A) <span class="zorunlu">*</span></label><input type="text" name="sinif" class="form-control" required></div>
                            <div class="col-md-4"><label class="form-label">Okul Numarası <span class="zorunlu">*</span></label><input type="text" name="okul_no" class="form-control" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required></div>
                            <div class="col-md-4"><label class="form-label">T.C. Kimlik No <span class="zorunlu">*</span></label><input type="text" name="tc_kimlik" class="form-control" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);" required></div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Öğrenci Telefonu (Opsiyonel)</label>
                                <input type="text" name="ogrenci_telefon_input" class="form-control" placeholder="5XX XXX XX XX" maxlength="14" oninput="formatTelefon(this)">
                            </div>
                            
                            <div class="col-md-6"><label class="form-label">Belirlenecek Şifre <span class="zorunlu">*</span></label><input type="password" name="sifre" class="form-control" required></div>
                            
                            <div class="col-12 mt-4"><hr style="border-color: rgba(91,192,190,0.3);"><h6 class="neon-text"><i class="fa-solid fa-link me-2"></i>Veli Bağlantı Bilgileri</h6><p style="font-size:0.75rem; color:#A0B2C6;">Velinizin sisteme girdiğinde sizi bulabilmesi için bu bilgileri eksiksiz doldurun.</p></div>
                            
                            <div class="col-md-6"><label class="form-label">Veli Adı Soyadı <span class="zorunlu">*</span></label><input type="text" name="veli_adi" class="form-control" required></div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Veli Telefonu (Çok Önemli!) <span class="zorunlu">*</span></label>
                                <input type="text" name="veli_telefon_input" class="form-control" placeholder="5XX XXX XX XX" maxlength="14" oninput="formatTelefon(this)" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-neon w-100 mt-4 py-2"><i class="fa-solid fa-user-plus me-2"></i> Öğrenci Kaydını Tamamla</button>
                    </form>
                </div>

                <div class="tab-pane fade" id="veli">
                    <form action="register.php" method="POST">
                        <input type="hidden" name="kayit_turu" value="veli">
                        <div class="alert alert-info" style="background: rgba(91,192,190,0.1); border: 1px solid var(--neon-blue); color: var(--text-light); font-size: 0.85rem;">
                            <i class="fa-solid fa-circle-info me-2 text-info"></i> Lütfen çocuğunuzun okul sistemine verdiği telefon numaranız ile kayıt olunuz. Sistem sizi otomatik eşleştirecektir.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Adınız <span class="zorunlu">*</span></label><input type="text" name="adi" class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label">Soyadınız <span class="zorunlu">*</span></label><input type="text" name="soyadi" class="form-control" required></div>
                            <div class="col-md-12"><label class="form-label">T.C. Kimlik No <span class="zorunlu">*</span></label><input type="text" name="tc_kimlik" class="form-control" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);" required></div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Sisteme Kayıtlı Telefonunuz <span class="zorunlu">*</span></label>
                                <input type="text" name="veli_kendi_telefon_input" class="form-control" placeholder="5XX XXX XX XX" maxlength="14" oninput="formatTelefon(this)" required>
                            </div>
                            
                            <div class="col-md-6"><label class="form-label">E-Posta Adresiniz <span class="zorunlu">*</span></label><input type="email" name="email" class="form-control" required></div>
                            <div class="col-md-12"><label class="form-label">Hesap Şifreniz <span class="zorunlu">*</span></label><input type="password" name="sifre" class="form-control" required></div>
                        </div>
                        <button type="submit" class="btn btn-neon w-100 mt-4 py-2" style="border-color: #f6c23e; color: #f6c23e;"><i class="fa-solid fa-user-shield me-2"></i> Veli Kaydını Tamamla</button>
                    </form>
                </div>
                
            </div>
            
            <div class="text-center mt-4">
                <a href="login.php" class="text-decoration-none" style="font-size: 0.95rem; color: #E6F1F9;">Zaten bir hesabınız var mı? <span class="neon-text fw-bold">Giriş Yapın</span></a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    
    <script>
        // SİSTEMİ HACKLEYEN YEPYENİ VE ZORBALIK YAPAN FORMAT FONKSİYONU!
        // (App.js'in buna müdahale etme şansı SIFIRDIR!)
        function formatTelefon(inputElement) {
            // Sadece ve sadece rakamları al
            let sayilar = inputElement.value.replace(/\D/g, '');
            
            // Eğer rakam girildiyse ve 5 ile başlamıyorsa, başına 5 ekle (0 girerse siler, başka girerse 5 ekler)
            if (sayilar.length > 0) {
                if (sayilar[0] === '0') {
                    sayilar = sayilar.substring(1);
                }
                if (sayilar.length > 0 && sayilar[0] !== '5') {
                    sayilar = '5' + sayilar;
                }
            }
            
            // En fazla 10 haneli rakam tut
            sayilar = sayilar.substring(0, 10);
            
            // Rakamları kusursuzca 3 - 3 - 2 - 2 şeklinde diz
            let formatli = '';
            for (let i = 0; i < sayilar.length; i++) {
                if (i === 3 || i === 6 || i === 8) {
                    formatli += ' ';
                }
                formatli += sayilar[i];
            }
            
            // Kutuya zorla yazdır!
            inputElement.value = formatli;
        }
    </script>
    
    <script src="assets/js/app.js?v=99"></script>

    <?php if($mesaj != ''): ?>
    <script>
        Swal.fire({
            icon: '<?php echo $mesaj_tur; ?>',
            title: '<?php echo $mesaj_tur == "success" ? "Başarılı!" : "Hata!"; ?>',
            text: '<?php echo $mesaj; ?>',
            background: '#1C2541', color: '#fff', confirmButtonColor: '#5BC0BE'
        }).then((result) => {
            if (result.isConfirmed && '<?php echo $mesaj_tur; ?>' == 'success') {
                window.location.href = 'login.php';
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>