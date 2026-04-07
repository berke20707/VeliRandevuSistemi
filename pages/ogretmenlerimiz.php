<?php
require_once '../includes/auth_check.php';
require_once '../includes/ogretmen_data.php';

$user_id = $_SESSION['user_id'];
$ad_soyad = $_SESSION['ad_soyad'];
$rol = $_SESSION['rol'] ?? 'veli';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Öğretmen Kadromuz | Ahi Evran MTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* SADELEŞTİRİLMİŞ VİTRİN KART TASARIMI */
        .teacher-card { background: rgba(11, 19, 43, 0.6); border: 1px solid rgba(255,255,255,0.05); border-radius: 15px; padding: 25px 20px 20px 20px; text-align: center; transition: all 0.3s ease; position: relative; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: space-between; }
        .teacher-card:hover { transform: translateY(-5px); border-color: rgba(91, 192, 190, 0.5); box-shadow: 0 5px 20px rgba(91, 192, 190, 0.1); background: rgba(11, 19, 43, 0.8); }
        .avatar-circle { width: 75px; height: 75px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: bold; margin-bottom: 15px; background: transparent; border: 2px solid rgba(255,255,255,0.2); color: #fff; transition: 0.3s;}
        .teacher-card:hover .avatar-circle { border-color: #5BC0BE; color: #5BC0BE; }
        
        /* MÜDÜR İÇİN ÖZEL KART TASARIMI */
        .chief-card { border: 1px solid rgba(255, 215, 0, 0.3); background: rgba(20, 25, 45, 0.8); min-width: 280px; }
        .chief-card:hover { border-color: rgba(255, 215, 0, 0.8); box-shadow: 0 5px 20px rgba(255, 215, 0, 0.15); }
        .chief-card .avatar-circle { border-color: rgba(255, 215, 0, 0.5); color: #FFD700; }
        .chief-card:hover .avatar-circle { border-color: #FFD700; }
        .chief-badge { background: rgba(255, 215, 0, 0.1); color: #FFD700; border: 1px solid rgba(255, 215, 0, 0.2); width: 100%; padding: 8px; border-radius: 8px; font-size: 0.85rem; font-weight: bold; margin-top: 15px; }

        /* TÜM ÖĞRETMENLER İÇİN STANDART KART GENİŞLİĞİ (Dengeyi sağlamak için) */
        .standard-card { min-width: 240px; max-width: 280px; width: 100%; flex: 1 1 240px; }

        .card-badge { background: rgba(255,255,255,0.05); color: #A0B2C6; border: 1px solid rgba(255,255,255,0.1); width: 100%; padding: 8px; border-radius: 8px; font-size: 0.8rem; font-weight: bold; margin-top: 15px; }
        .theme-own .card-badge { background: rgba(91, 192, 190, 0.1); color: #5BC0BE; border-color: rgba(91, 192, 190, 0.3); }
        
        .status-dot { width: 12px; height: 12px; border-radius: 50%; position: absolute; top: 15px; left: 15px; box-shadow: 0 0 8px currentColor; }
        .online { background: #28a745; color: #28a745; } .offline { background: #dc3545; color: #dc3545; }
        .is-own::before { content: "ÖĞRENCİNİN HOCASI"; position: absolute; top: 10px; right: 10px; font-size: 0.6rem; background: #5BC0BE; color: #0B132B; padding: 3px 10px; border-radius: 15px; font-weight: bold; letter-spacing: 0.5px;}

        /* Arama Konteyneri */
        .controls-container { display: flex; align-items: center; justify-content: center; margin-bottom: 40px; background: rgba(0,0,0,0.2); padding: 20px; border-radius: 15px; border: 1px solid rgba(91, 192, 190, 0.1); }
        .search-container { position: relative; width: 100%; max-width: 600px; }
        .search-input { width: 100%; padding: 12px 25px 12px 45px; border-radius: 50px; background: rgba(0,0,0,0.5); border: 1px solid var(--neon-blue); color: #fff; font-size: 1rem; transition: all 0.3s; }
        .search-input:focus { outline: none; box-shadow: 0 0 15px rgba(91, 192, 190, 0.3); background: rgba(0,0,0,0.7); }
        .search-icon { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--neon-blue); }

        .group-title { border-bottom: 2px solid var(--neon-blue); padding-bottom: 10px; display: inline-block; margin-top: 30px; margin-bottom: 20px; font-weight: bold; }
        
        /* Çizgi tasarımları için (Hiyerarşi hissi) */
        .hierarchy-line { width: 2px; height: 30px; background: rgba(91, 192, 190, 0.3); margin: 0 auto; }
    </style>
</head>
<body class="bg-dark-space">
    <div id="particles-js" style="position: fixed; z-index: -1;"></div>

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
        <a href="ogretmenlerimiz.php" class="sidebar-link active" style="background: rgba(91, 192, 190, 0.15); border-right: 4px solid var(--neon-blue);"><i class="fa-solid fa-users-viewfinder"></i> <span class="sidebar-text">Eğitim Kadrosu</span></a>
        <a href="profil.php" class="sidebar-link"><i class="fa-solid fa-user-gear"></i> <span class="sidebar-text">Profilim</span></a>
        <div style="position: absolute; bottom: 20px; width: 100%;">
            <a href="../logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket text-danger"></i> <span class="sidebar-text">Güvenli Çıkış</span></a>
        </div>
    </nav>

    <div class="main-content" style="padding: 40px;">
        
        <div class="d-flex justify-content-between align-items-center mb-4 slide-up-fade">
            <h3 class="text-light fw-bold m-0">Öğretmen <span class="neon-text">Kadromuz</span></h3>
            <div class="d-flex align-items-center gap-4">
                <div class="text-light" style="font-size: 0.9rem; font-weight: 500; opacity: 0.9;">
                    <i class="fa-solid fa-school text-info me-1"></i> Ahi Evran Mesleki Ve Teknik Anadolu Lisesi
                </div>
                <button class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 40px; height: 40px; border-color: rgba(255,255,255,0.2); transition: all 0.3s; background: rgba(0,0,0,0.3);" onclick="toggleThemeMode()" title="Gündüz/Gece Modu">
                    <i id="theme-icon-indicator" class="fa-solid fa-moon text-light"></i>
                </button>
            </div>
        </div>

        <div class="controls-container slide-up-fade">
            <div class="search-container m-0">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Öğretmen adı veya branş ara..." onkeyup="filterTeachers()">
            </div>
        </div>

        <div id="allTeachersContainer">
            
            <?php if(!empty($mudur)): ?>
            <div class="category-section slide-up-fade">
                <div class="d-flex justify-content-center mb-3">
                    <?php foreach($mudur as $kisi): ?>
                    <div class="teacher-item <?php echo $kisi['kendi_hocasi'] ? 'own-teacher-item' : 'other-teacher-item'; ?>">
                        <div class="teacher-card chief-card <?php echo $kisi['kendi_hocasi'] ? 'theme-own is-own' : ''; ?>">
                            <div class="status-dot <?php echo $kisi['okulda'] ? 'online' : 'offline'; ?>" title="<?php echo $kisi['okulda'] ? 'Okulda' : 'Okulda Değil'; ?>"></div>
                            <div class="avatar-circle"><?php echo getInitials($kisi['ad']); ?></div>
                            <div class="mb-auto w-100">
                                <small style="color: #FFD700; font-size:0.8rem; font-weight: bold; letter-spacing: 1px;">OKUL MÜDÜRÜ</small>
                                <h5 class="text-light fw-bold m-0 mt-2"><?php echo $kisi['ad']; ?></h5>
                            </div>
                            <div class="chief-badge"><i class="fa-solid fa-crown me-1"></i> Yönetim Kurulu</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="hierarchy-line"></div>
            </div>
            <?php endif; ?>

            <?php if(!empty($mudur_yardimcilari)): ?>
            <div class="category-section slide-up-fade">
                <div class="d-flex justify-content-center flex-wrap gap-4 mb-5">
                    <?php foreach($mudur_yardimcilari as $kisi): ?>
                    <div class="teacher-item <?php echo $kisi['kendi_hocasi'] ? 'own-teacher-item' : 'other-teacher-item'; ?>">
                        <div class="teacher-card standard-card <?php echo $kisi['kendi_hocasi'] ? 'theme-own is-own' : 'theme-normal'; ?>">
                            <div class="status-dot <?php echo $kisi['okulda'] ? 'online' : 'offline'; ?>" title="<?php echo $kisi['okulda'] ? 'Okulda' : 'Okulda Değil'; ?>"></div>
                            <div class="avatar-circle"><?php echo getInitials($kisi['ad']); ?></div>
                            <div class="mb-auto w-100">
                                <small style="color: <?php echo $kisi['kendi_hocasi'] ? '#5BC0BE' : '#A0B2C6'; ?>; font-size:0.75rem; font-weight: bold;"><?php echo $kisi['brans']; ?></small>
                                <h6 class="text-light fw-bold m-0 mt-2" style="font-size:1.05rem;"><?php echo $kisi['ad']; ?></h6>
                            </div>
                            <div class="card-badge"><i class="fa-solid fa-user-shield me-1"></i> İdari Kadro</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php foreach($gruplar as $grup_adi => $uyeler): ?>
                <div class="category-section slide-up-fade text-center">
                    <h4 class="text-info group-title text-center">
                        <i class="fa-solid fa-layer-group me-2" style="font-size: 0.8em;"></i><?php echo $grup_adi; ?>
                    </h4>
                    <div class="d-flex justify-content-center flex-wrap gap-4 mb-5">
                        <?php foreach($uyeler as $kisi): ?>
                        <div class="teacher-item <?php echo $kisi['kendi_hocasi'] ? 'own-teacher-item' : 'other-teacher-item'; ?>">
                            <div class="teacher-card standard-card <?php echo $kisi['kendi_hocasi'] ? 'theme-own is-own' : 'theme-normal'; ?>">
                                <div class="status-dot <?php echo $kisi['okulda'] ? 'online' : 'offline'; ?>" title="<?php echo $kisi['okulda'] ? 'Okulda' : 'Okulda Değil'; ?>"></div>
                                <div class="avatar-circle"><?php echo getInitials($kisi['ad']); ?></div>
                                <div class="mb-auto w-100">
                                    <small style="color: <?php echo $kisi['kendi_hocasi'] ? '#5BC0BE' : '#A0B2C6'; ?>; font-size:0.75rem; font-weight: bold;"><?php echo $kisi['brans']; ?></small>
                                    <h6 class="text-light fw-bold m-0 mt-2" style="font-size:1.1rem;"><?php echo $kisi['ad']; ?></h6>
                                </div>
                                <div class="card-badge"><i class="fa-solid fa-user-tie me-1"></i> Eğitim Kadrosu</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        particlesJS.load('particles-js', '../assets/js/particles.json');

        function filterTeachers() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let categories = document.getElementsByClassName('category-section');

            for (let cat of categories) {
                let items = cat.getElementsByClassName('teacher-item');
                let visibleCount = 0;

                for (let item of items) {
                    // İsim veya branşta arama
                    let match = item.textContent.toLowerCase().includes(input);

                    if (!match) { 
                        item.style.display = "none"; 
                    } else { 
                        item.style.display = "block"; 
                        visibleCount++; 
                    }
                }
                
                // Grupta hiç öğretmen kalmadıysa o bölümün başlığını da gizle
                cat.style.display = (visibleCount === 0) ? "none" : "block";
            }
        }
    </script>
</body>
</html>