<?php
session_start();
require_once '../config/database.php';

// Güvenlik Duvarı: Sadece Veliler Girebilir!
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'veli') {
    header("Location: ../login.php");
    exit;
}

$veli_telefon = $_SESSION['telefon'];
$veli_ad_soyad = $_SESSION['ad_soyad'];

// Öğrenci Seçimi Yapıldıysa (Tıklandığında Burası Çalışır)
if (isset($_POST['secilen_ogrenci_id'])) {
    $_SESSION['aktif_ogrenci_id'] = $_POST['secilen_ogrenci_id'];
    $_SESSION['aktif_ogrenci_ad'] = $_POST['secilen_ogrenci_ad'];
    $_SESSION['aktif_ogrenci_sinif'] = $_POST['secilen_ogrenci_sinif'];
    
    // Çocuğu hafızaya aldık, şimdi Ana Panele uçuyoruz!
    header("Location: dashboard.php");
    exit;
}

// Veritabanından velinin telefon numarasına kayıtlı çocukları bulalım
$stmt = $db->prepare("SELECT * FROM ogrenciler WHERE veli_telefon = ?");
$stmt->execute([$veli_telefon]);
$ogrenciler = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Seçimi | Veli Portalı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=23">
    <style>
        /* ÖĞRENCİ SEÇİM EKRANI ÖZEL CSS */
        .student-card {
            background: rgba(28, 37, 65, 0.85);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(91, 192, 190, 0.3);
            border-radius: 20px;
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            height: 100%;
        }
        
        .student-card::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(91,192,190,0.1) 0%, transparent 70%);
            transform: rotate(45deg); opacity: 0; transition: opacity 0.5s; z-index: 0;
        }

        .student-card:hover {
            transform: translateY(-15px) scale(1.05);
            box-shadow: 0 20px 40px rgba(0,0,0,0.6), 0 0 20px rgba(91, 192, 190, 0.4);
            border-color: var(--neon-blue);
        }
        
        .student-card:hover::before { opacity: 1; }

        .student-avatar {
            width: 90px; height: 90px;
            border-radius: 50%;
            background: var(--space-dark);
            border: 3px solid var(--neon-blue);
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; color: var(--neon-blue);
            margin: 0 auto 20px auto;
            position: relative; z-index: 1;
            box-shadow: 0 0 15px rgba(91, 192, 190, 0.5);
            transition: all 0.3s;
        }

        .student-card:hover .student-avatar {
            background: var(--neon-blue); color: var(--space-dark); transform: rotate(360deg);
        }

        .student-info { position: relative; z-index: 1; }
        
        .btn-select {
            background: transparent; border: 1px solid var(--neon-blue); color: var(--neon-blue);
            border-radius: 30px; padding: 8px 25px; font-weight: bold; margin-top: 20px; transition: all 0.3s;
        }
        
        .student-card:hover .btn-select { background: var(--neon-blue); color: var(--space-dark); }
    </style>
</head>
<body class="bg-dark-space d-flex flex-column align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="school-watermark"><i class="fa-solid fa-graduation-cap"></i></div>
    <div id="particles-js" style="position: fixed; z-index: -1;"></div>

    <div style="position: absolute; top: 30px; right: 40px; z-index: 999;">
        <button class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 45px; height: 45px; border-color: rgba(255,255,255,0.2); transition: all 0.3s; background: rgba(0,0,0,0.3);" onclick="toggleThemeMode()" title="Gündüz/Gece Modu">
            <i id="theme-icon-indicator" class="fa-solid fa-moon text-light"></i>
        </button>
    </div>

    <div class="container text-center" style="z-index: 1;">
        
        <div class="mb-5 slide-up-fade" style="animation-delay: 0.1s;">
            <img src="../assets/img/logo.png" alt="Okul Logosu" style="width: 100px; filter: drop-shadow(0 0 15px rgba(91,192,190,0.6)); mb-3">
            <h2 class="text-light fw-bold mt-3">Hoş Geldiniz, <span class="neon-text"><?php echo htmlspecialchars($veli_ad_soyad); ?></span></h2>
            <p style="color: #A0B2C6; font-size: 1.1rem;">Lütfen işlem yapmak istediğiniz öğrenciyi seçin</p>
        </div>

        <div class="row justify-content-center g-4">
            
            <?php if (count($ogrenciler) > 0): ?>
                <?php $gecikme = 0.2; foreach($ogrenciler as $ogrenci): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 slide-up-fade" style="animation-delay: <?php echo $gecikme; ?>s;">
                        <form action="ogrenci_sec.php" method="POST" class="h-100">
                            <input type="hidden" name="secilen_ogrenci_id" value="<?php echo $ogrenci['id']; ?>">
                            <input type="hidden" name="secilen_ogrenci_ad" value="<?php echo htmlspecialchars($ogrenci['adi'] . ' ' . $ogrenci['soyadi']); ?>">
                            <input type="hidden" name="secilen_ogrenci_sinif" value="<?php echo htmlspecialchars($ogrenci['sinif']); ?>">
                            
                            <div class="student-card tilt-card" onclick="this.parentNode.submit();">
                                <div class="student-avatar">
                                    <i class="fa-solid fa-user-graduate"></i>
                                </div>
                                <div class="student-info">
                                    <h4 class="text-light fw-bold mb-1"><?php echo htmlspecialchars($ogrenci['adi'] . ' ' . $ogrenci['soyadi']); ?></h4>
                                    <div class="d-flex justify-content-center gap-2 mt-2 mb-3">
                                        <span class="badge" style="background: rgba(91,192,190,0.2); border: 1px solid var(--neon-blue); color: var(--neon-blue);"><i class="fa-solid fa-chalkboard text-light me-1"></i> <?php echo htmlspecialchars($ogrenci['sinif']); ?></span>
                                        <span class="badge" style="background: rgba(246, 194, 62, 0.2); border: 1px solid #f6c23e; color: #f6c23e;"><i class="fa-solid fa-hashtag text-light me-1"></i> <?php echo htmlspecialchars($ogrenci['okul_no']); ?></span>
                                    </div>
                                    <button type="button" class="btn btn-select">Seç ve Devam Et <i class="fa-solid fa-arrow-right ms-1"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php $gecikme += 0.1; endforeach; ?>
            <?php else: ?>
                <div class="col-md-6 slide-up-fade" style="animation-delay: 0.2s;">
                    <div class="glass-card p-5 text-center" style="border-color: #dc3545;">
                        <i class="fa-solid fa-triangle-exclamation fa-4x text-danger mb-4 fa-fade"></i>
                        <h4 class="text-light fw-bold">Kayıtlı Öğrenci Bulunamadı!</h4>
                        <p style="color: #A0B2C6;">Sistemde <b><?php echo htmlspecialchars($veli_telefon); ?></b> numarasına kayıtlı bir öğrenci görünmüyor. Öğrenciniz kayıt olurken veli numarasını hatalı girmiş olabilir.</p>
                        <a href="../logout.php" class="btn btn-outline-danger mt-3"><i class="fa-solid fa-arrow-left me-2"></i> Çıkış Yap</a>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <div class="mt-5 slide-up-fade" style="animation-delay: 0.8s;">
            <a href="../logout.php" class="text-danger text-decoration-none" style="font-size: 0.9rem; opacity: 0.8; transition: opacity 0.3s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Farklı bir hesaba geç / Çıkış
            </a>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.0/vanilla-tilt.min.js"></script>
    <script src="../assets/js/app.js?v=23"></script>
    <script>
         else {
                icon.className = 'fa-solid fa-moon text-light';
                icon.style.color = '#fff';
            }
        }
    </script>
</body>
</html>