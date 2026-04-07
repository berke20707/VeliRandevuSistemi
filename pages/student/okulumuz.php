<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

if ($_SESSION['rol'] !== 'ogrenci') {
    header("Location: ../dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$ad_soyad = $_SESSION['ad_soyad'];

$ogrenci_stmt = $db->prepare("SELECT * FROM ogrenciler WHERE id = ?");
$ogrenci_stmt->execute([$user_id]);
$ogrenci = $ogrenci_stmt->fetch(PDO::FETCH_ASSOC);
$sinif = $ogrenci['sinif'] ?? 'Bilinmiyor';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Okulumuz Hakkında | Ahi Evran MTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        body { transition: background 0.5s ease, color 0.5s ease; }
        .about-hero { background: linear-gradient(135deg, rgba(28, 37, 65, 0.95), rgba(91, 192, 190, 0.1)); border: 1px solid rgba(91, 192, 190, 0.3); border-radius: 20px; padding: 40px; position: relative; overflow: hidden; margin-bottom: 30px; }
        .about-hero::before { content: ''; position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(91,192,190,0.06) 0%, transparent 70%); border-radius: 50%; }
        .about-hero::after { content: ''; position: absolute; bottom: -80px; left: -80px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(246,194,62,0.04) 0%, transparent 70%); border-radius: 50%; }
        .about-section { background: rgba(28, 37, 65, 0.6); border: 1px solid rgba(91, 192, 190, 0.15); border-radius: 15px; padding: 25px; margin-bottom: 20px; transition: all 0.3s; }
        .about-section:hover { border-color: rgba(91, 192, 190, 0.4); transform: translateY(-2px); }
        .about-section h5 { color: #fff; font-weight: bold; margin-bottom: 12px; }
        .about-section p { color: #A0B2C6; font-size: 0.9rem; line-height: 1.7; margin: 0; }
        .about-section .section-icon { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 15px; }
        .stat-box { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 20px; text-align: center; }
        .stat-box h3 { color: var(--neon-blue); font-weight: bold; margin: 0; }
        .stat-box p { color: #A0B2C6; font-size: 0.8rem; margin: 5px 0 0 0; }
        .alan-card { background: rgba(28, 37, 65, 0.7); border: 1px solid rgba(91, 192, 190, 0.2); border-radius: 15px; padding: 20px; text-align: center; transition: all 0.3s; }
        .alan-card:hover { transform: translateY(-5px); border-color: var(--neon-blue); box-shadow: 0 10px 25px rgba(91, 192, 190, 0.15); }
        .alan-card .alan-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 1.5rem; }
    </style>
</head>
<body class="bg-dark-space">
    <div class="school-watermark"><i class="fa-solid fa-graduation-cap"></i></div>
    <div id="particles-js" style="position: fixed; z-index: -1;"></div>

    <nav class="glass-sidebar">
        <div class="text-center mb-5 mt-3 px-2 text-light fw-bold" style="border-bottom: 1px solid rgba(91, 192, 190, 0.2); padding-bottom: 20px;">
            <img src="../../assets/img/logo.png" alt="Ahi Evran MTAL Logosu" class="sidebar-logo mb-3">
            <br>
            <span class="sidebar-text" style="font-size: 0.9rem; line-height: 1.5; display: block;">
                Ahi Evran Mesleki Ve Teknik<br>Anadolu Lisesi<br>
            </span>
        </div>
        <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-house"></i> <span class="sidebar-text">Ana Panel</span></a>
        <a href="okulumuz.php" class="sidebar-link" style="background: rgba(91, 192, 190, 0.15); border-right: 4px solid var(--neon-blue);"><i class="fa-solid fa-school-flag"></i> <span class="sidebar-text">Okulumuz Hakkında</span></a>
        <a href="../ogretmenlerimiz.php" class="sidebar-link"><i class="fa-solid fa-users-viewfinder"></i> <span class="sidebar-text">Eğitim Kadrosu</span></a>
        <a href="../profil.php" class="sidebar-link"><i class="fa-solid fa-user-gear"></i> <span class="sidebar-text">Profilim</span></a>

        <div style="position: absolute; bottom: 20px; width: 100%;">
            <a href="../../logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket text-danger"></i> <span class="sidebar-text">Güvenli Çıkış</span></a>
        </div>
    </nav>

    <div class="main-content" style="padding: 30px 50px;">
        <!-- Üst bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 slide-up-fade">
            <h2 class="m-0 fw-bold glow-title" style="font-size: 1.8rem; letter-spacing: 1px;">Okulumuz Hakkında</h2>
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 40px; height: 40px; border-color: rgba(255,255,255,0.2); transition: all 0.3s; background: rgba(0,0,0,0.3);" onclick="toggleThemeMode()" title="Gündüz/Gece Modu">
                    <i id="theme-icon-indicator" class="fa-solid fa-moon text-light"></i>
                </button>
            </div>
        </div>

        <!-- Hero Bölümü -->
        <div class="about-hero slide-up-fade" style="animation-delay: 0.1s;">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center mb-3">
                        <img src="../../assets/img/logo.png" alt="Logo" style="width: 70px; filter: drop-shadow(0 0 10px rgba(91,192,190,0.4)); margin-right: 20px;">
                        <div>
                            <h2 class="text-light fw-bold m-0">Ahi Evran Mesleki ve Teknik Anadolu Lisesi</h2>
                            <p class="m-0 mt-1" style="color: var(--neon-blue); font-size: 1rem;">Çorlu / Tekirdağ</p>
                        </div>
                    </div>
                    <p style="color: #A0B2C6; font-size: 0.95rem; line-height: 1.8; max-width: 700px;">
                        Ahi Evran Mesleki ve Teknik Anadolu Lisesi, Tekirdağ'ın Çorlu ilçesinde faaliyet gösteren, 
                        öğrencilerine hem akademik hem de mesleki anlamda üstün bir eğitim sunan köklü bir eğitim kurumudur. 
                        Okulumuz, modern atölyeleri ve deneyimli kadrosuyla sektörün ihtiyaç duyduğu nitelikli ara elemanlar yetiştirmektedir.
                    </p>
                </div>
                <div class="col-lg-4 text-center d-none d-lg-block" style="position: relative; z-index: 1;">
                    <i class="fa-solid fa-school" style="font-size: 6rem; color: rgba(91, 192, 190, 0.12);"></i>
                </div>
            </div>
        </div>

        <!-- İstatistikler -->
        <div class="row g-3 mb-4 slide-up-fade" style="animation-delay: 0.15s;">
            <div class="col-md-3 col-6"><div class="stat-box"><h3>4</h3><p>Meslek Alanı</p></div></div>
            <div class="col-md-3 col-6"><div class="stat-box"><h3>100+</h3><p>Eğitim Kadrosu</p></div></div>
            <div class="col-md-3 col-6"><div class="stat-box"><h3>2000+</h3><p>Aktif Öğrenci</p></div></div>
            <div class="col-md-3 col-6"><div class="stat-box"><h3>1985</h3><p>Kuruluş Yılı</p></div></div>
        </div>

        <!-- Alanlar -->
        <h5 class="text-light fw-bold mb-3 slide-up-fade" style="animation-delay: 0.2s;"><i class="fa-solid fa-layer-group neon-text me-2"></i>Meslek Alanlarımız</h5>
        <div class="row g-4 mb-4 slide-up-fade" style="animation-delay: 0.25s;">
            <div class="col-lg-3 col-md-6">
                <div class="alan-card">
                    <div class="alan-icon" style="background: rgba(91, 192, 190, 0.15); color: #5BC0BE;"><i class="fa-solid fa-microchip"></i></div>
                    <h6 class="text-light fw-bold">Bilişim Teknolojileri</h6>
                    <p style="font-size: 0.8rem; color: #A0B2C6;">Yazılım, donanım, ağ teknolojileri ve web geliştirme alanlarında eğitim.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="alan-card">
                    <div class="alan-icon" style="background: rgba(246, 194, 62, 0.15); color: #f6c23e;"><i class="fa-solid fa-bolt"></i></div>
                    <h6 class="text-light fw-bold">Elektrik-Elektronik</h6>
                    <p style="font-size: 0.8rem; color: #A0B2C6;">Elektrik tesisatı, elektronik devre tasarımı ve endüstriyel uygulamalar.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="alan-card">
                    <div class="alan-icon" style="background: rgba(40, 167, 69, 0.15); color: #28a745;"><i class="fa-solid fa-gears"></i></div>
                    <h6 class="text-light fw-bold">Endüstriyel Otomasyon</h6>
                    <p style="font-size: 0.8rem; color: #A0B2C6;">PLC programlama, otomasyon sistemleri ve endüstriyel kontrol.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="alan-card">
                    <div class="alan-icon" style="background: rgba(220, 53, 69, 0.15); color: #dc3545;"><i class="fa-solid fa-car"></i></div>
                    <h6 class="text-light fw-bold">Motorlu Araçlar</h6>
                    <p style="font-size: 0.8rem; color: #A0B2C6;">Motor bakım-onarım, araç elektroniği ve otomotiv teknolojileri.</p>
                </div>
            </div>
        </div>

        <!-- Vizyon & Misyon -->
        <div class="row g-4 mb-4 slide-up-fade" style="animation-delay: 0.3s;">
            <div class="col-md-6">
                <div class="about-section h-100">
                    <div class="section-icon" style="background: rgba(91, 192, 190, 0.15); color: #5BC0BE;"><i class="fa-solid fa-eye"></i></div>
                    <h5><i class="fa-solid fa-bullseye text-info me-2"></i>Vizyonumuz</h5>
                    <p>Teknolojiyi takip eden, çağdaş değerlere sahip, mesleki yeterliliği yüksek, araştırmacı ve üretken bireyler yetiştiren, 
                    toplumun güvenini kazanmış, tercih edilen bir eğitim kurumu olmak.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="about-section h-100">
                    <div class="section-icon" style="background: rgba(246, 194, 62, 0.15); color: #f6c23e;"><i class="fa-solid fa-rocket"></i></div>
                    <h5><i class="fa-solid fa-compass text-warning me-2"></i>Misyonumuz</h5>
                    <p>Öğrencilerimizi ulusal ve uluslararası düzeyde rekabet edebilecek mesleki bilgi ve becerilerle donatmak, 
                    onları iş dünyasına ve yükseköğretime hazırlamak, Atatürk ilke ve inkılâplarına bağlı nesiller yetiştirmek.</p>
                </div>
            </div>
        </div>

        <!-- İletişim -->
        <div class="about-section slide-up-fade" style="animation-delay: 0.35s;">
            <h5><i class="fa-solid fa-location-dot neon-text me-2"></i>İletişim Bilgileri</h5>
            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-3 p-3" style="background: rgba(0,0,0,0.2); border-radius: 10px;">
                        <i class="fa-solid fa-map-marker-alt text-info fs-4"></i>
                        <div>
                            <p class="text-light fw-bold m-0" style="font-size: 0.85rem;">Adres</p>
                            <p class="m-0" style="font-size: 0.8rem; color: #A0B2C6;">Çorlu / Tekirdağ</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-3 p-3" style="background: rgba(0,0,0,0.2); border-radius: 10px;">
                        <i class="fa-solid fa-phone text-success fs-4"></i>
                        <div>
                            <p class="text-light fw-bold m-0" style="font-size: 0.85rem;">Telefon</p>
                            <p class="m-0" style="font-size: 0.8rem; color: #A0B2C6;">(282) 673 XX XX</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <a href="https://corluahievranmtal.meb.k12.tr" target="_blank" class="d-flex align-items-center gap-3 p-3 text-decoration-none" style="background: rgba(0,0,0,0.2); border-radius: 10px;">
                        <i class="fa-solid fa-globe text-warning fs-4"></i>
                        <div>
                            <p class="text-light fw-bold m-0" style="font-size: 0.85rem;">Web Sitesi</p>
                            <p class="m-0" style="font-size: 0.8rem; color: #A0B2C6;">corluahievranmtal.meb.k12.tr</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="../../assets/js/app.js?v=<?php echo time(); ?>"></script>
    <script>
         else {
                icon.classList.remove('fa-sun'); icon.classList.add('fa-moon'); icon.style.color = '#fff';
            }
        }
    </script>
</body>
</html>
