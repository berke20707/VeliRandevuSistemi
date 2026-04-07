<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

if ($_SESSION['rol'] !== 'yonetici') {
    header("Location: ../dashboard.php");
    exit;
}

// Müdür bilgilerini veritabanından dinamik çek
$mudur_stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND rol = 'yonetici'");
$mudur_stmt->execute([$_SESSION['user_id']]);
$mudur = $mudur_stmt->fetch();

// Eğer müdür bilgileri değişmişse session'ı da güncelle
if ($mudur) {
    $_SESSION['ad_soyad'] = $mudur['adi'] . ' ' . $mudur['soyadi'];
}

$ad_soyad = $_SESSION['ad_soyad'];

// İstatistikler 
$veli_sayisi = $db->query("SELECT count(*) FROM users WHERE rol='veli' AND silindi_mi=0")->fetchColumn();
$ogrenci_sayisi = $db->query("SELECT count(*) FROM ogrenciler")->fetchColumn();
$ogretmen_sayisi = $db->query("SELECT count(*) FROM users WHERE rol='ogretmen' AND silindi_mi=0")->fetchColumn();
$bugun = date('Y-m-d');
$bugunku_randevular = $db->query("SELECT count(*) FROM randevular WHERE tarih='$bugun'")->fetchColumn();
$toplam_randevu = $db->query("SELECT count(*) FROM randevular")->fetchColumn();
$bekleyen_randevu = $db->query("SELECT count(*) FROM randevular WHERE durum IN ('bekliyor','onaylandi') AND tarih >= '$bugun'")->fetchColumn();

// Duyuru sayısı
$duyuru_sayisi = $db->query("SELECT count(*) FROM announcements WHERE is_active=1")->fetchColumn();

// Kara liste sayısı
$blacklist_sayisi = $db->query("SELECT count(*) FROM blacklist")->fetchColumn();

// En çok randevu alan branş istatistiği
$brans_istatistik = $db->query("
    SELECT u.brans, COUNT(r.id) as randevu_sayisi 
    FROM randevular r 
    LEFT JOIN users u ON CONCAT(u.adi, ' ', u.soyadi) COLLATE utf8mb4_unicode_ci = r.ogretmen_ad COLLATE utf8mb4_unicode_ci 
    WHERE u.brans IS NOT NULL AND u.brans != ''
    GROUP BY u.brans 
    ORDER BY randevu_sayisi DESC 
    LIMIT 5
")->fetchAll();

// Bugünkü randevular (Canlı akış)
$hemen_randevular = $db->query("
    SELECT r.*, u.adi as veli_ad, u.soyadi as veli_soyad 
    FROM randevular r 
    LEFT JOIN users u ON r.veli_id = u.id 
    WHERE r.tarih = '$bugun' 
    ORDER BY r.saat ASC LIMIT 8
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müdür Paneli | Ahi Evran MTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .admin-stat-card { background: rgba(0,0,0,0.4); border: 1px solid rgba(91,192,190,0.3); border-radius: 15px; padding: 20px; transition: all 0.3s; position: relative; overflow: hidden; }
        .admin-stat-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(91,192,190,0.2); }
        .admin-stat-card .icon-box { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 15px; position: absolute; right: 20px; top: 20px; }
        .quick-action-btn { background: rgba(28, 37, 65, 0.7); border: 1px solid var(--neon-blue); border-radius: 10px; padding: 15px; text-align: center; color: #fff; text-decoration: none; display: block; transition: 0.3s; }
        .quick-action-btn:hover { background: var(--neon-blue); color: var(--space-dark); transform: scale(1.05); }
        .brans-bar { height: 8px; border-radius: 5px; background: rgba(0,0,0,0.3); overflow: hidden; margin-top: 5px; }
        .brans-bar-fill { height: 100%; border-radius: 5px; transition: width 0.5s ease; }
        .live-dot { width: 8px; height: 8px; border-radius: 50%; background: #28a745; display: inline-block; animation: pulse-live 1.5s infinite; margin-right: 6px; }
        @keyframes pulse-live { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
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
                <span class="neon-text" style="font-size: 0.75rem; font-weight: 400;">Bilişim & Yönetim Paneli</span>
            </span>
        </div>
        <a href="dashboard.php" class="sidebar-link active" style="background: rgba(91, 192, 190, 0.15); border-right: 4px solid var(--neon-blue);"><i class="fa-solid fa-gauge-high"></i> <span class="sidebar-text">Yönetim Paneli</span></a>
        <a href="teachers.php" class="sidebar-link"><i class="fa-solid fa-chalkboard-user"></i> <span class="sidebar-text">Eğitim Kadrosu</span></a>
        <a href="users.php" class="sidebar-link"><i class="fa-solid fa-users"></i> <span class="sidebar-text">Veli & Öğrenci VT</span></a>
        <a href="appointments.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> <span class="sidebar-text">Tüm Randevular</span></a>
        <a href="announcements.php" class="sidebar-link"><i class="fa-solid fa-bullhorn"></i> <span class="sidebar-text">Duyuru Yönetimi</span></a>
        <a href="blacklist.php" class="sidebar-link"><i class="fa-solid fa-user-xmark text-danger"></i> <span class="sidebar-text">Kara Liste</span></a>
        <a href="settings.php" class="sidebar-link"><i class="fa-solid fa-gears"></i> <span class="sidebar-text">Sistem Ayarları</span></a>
        
        <div style="position: absolute; bottom: 20px; width: 100%;">
            <a href="../../logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket text-danger"></i> <span class="sidebar-text">Güvenli Çıkış</span></a>
        </div>
    </nav>

    <div class="main-content" style="padding: 30px 50px;">
        <div class="row align-items-center mb-4">
            <div class="col-lg-6">
                <h4 class="text-light fw-bold m-0" style="font-size: 1.4rem;"><span class="neon-text"><?php echo htmlspecialchars($ad_soyad); ?></span> hoşgeldiniz.</h4>
                <p class="m-0 mt-1" style="color: #A0B2C6; font-size: 1rem;">Müdür & Yönetici Paneli</p>
            </div>
            <div class="col-lg-6 d-flex justify-content-end align-items-center">
                <button class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; border-color: rgba(255,255,255,0.2);" onclick="toggleThemeMode()">
                    <i id="theme-icon-indicator" class="fa-solid fa-moon text-light"></i>
                </button>
                <a href="settings.php#profil" class="text-decoration-none">
                    <div class="d-flex align-items-center p-1 rounded-pill shadow-sm" style="background: rgba(28, 37, 65, 0.9); border: 1px solid var(--neon-blue); padding-right: 15px !important;">
                        <div class="rounded-circle bg-dark d-flex justify-content-center align-items-center me-2 overflow-hidden" style="width: 45px; height: 45px; border: 1px solid var(--neon-blue);">
                            <i class="fa-solid fa-user-shield text-light" style="font-size: 1.2rem;"></i>
                        </div>
                        <div class="d-flex flex-column lh-1 pe-2">
                            <span class="text-light fw-bold" style="font-size: 0.95rem;"><?php echo htmlspecialchars($ad_soyad); ?></span>
                            <span style="font-size: 0.7rem; color: #f6c23e;">Okul Müdürü</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- İstatistikler -->
        <div class="row g-4 mb-4 slide-up-fade">
            <div class="col-xl-2 col-lg-3 col-md-6">
                <div class="admin-stat-card border-success">
                    <div class="icon-box bg-success text-white"><i class="fa-solid fa-calendar-check"></i></div>
                    <h3 class="text-success fw-bold"><?php echo $bugunku_randevular; ?></h3>
                    <p class="text-light m-0" style="font-size:0.85rem;">Bugünkü Randevular</p>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-6">
                <div class="admin-stat-card border-info">
                    <div class="icon-box bg-info text-white"><i class="fa-solid fa-chalkboard-user"></i></div>
                    <h3 class="text-info fw-bold"><?php echo $ogretmen_sayisi; ?></h3>
                    <p class="text-light m-0" style="font-size:0.85rem;">Aktif Öğretmen</p>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-6">
                <div class="admin-stat-card border-warning">
                    <div class="icon-box bg-warning text-white"><i class="fa-solid fa-graduation-cap"></i></div>
                    <h3 class="text-warning fw-bold"><?php echo $ogrenci_sayisi; ?></h3>
                    <p class="text-light m-0" style="font-size:0.85rem;">Kayıtlı Öğrenci</p>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-6">
                <div class="admin-stat-card" style="border-color: #e83e8c;">
                    <div class="icon-box text-white" style="background: #e83e8c;"><i class="fa-solid fa-users"></i></div>
                    <h3 class="fw-bold" style="color: #e83e8c;"><?php echo $veli_sayisi; ?></h3>
                    <p class="text-light m-0" style="font-size:0.85rem;">Sistemdeki Veliler</p>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-6">
                <div class="admin-stat-card" style="border-color: #6f42c1;">
                    <div class="icon-box text-white" style="background: #6f42c1;"><i class="fa-solid fa-clock-rotate-left"></i></div>
                    <h3 class="fw-bold" style="color: #6f42c1;"><?php echo $bekleyen_randevu; ?></h3>
                    <p class="text-light m-0" style="font-size:0.85rem;">Bekleyen Randevu</p>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-6">
                <div class="admin-stat-card" style="border-color: #dc3545;">
                    <div class="icon-box text-white" style="background: #dc3545;"><i class="fa-solid fa-user-xmark"></i></div>
                    <h3 class="fw-bold" style="color: #dc3545;"><?php echo $blacklist_sayisi; ?></h3>
                    <p class="text-light m-0" style="font-size:0.85rem;">Kara Listede</p>
                </div>
            </div>
        </div>

        <div class="row g-4 slide-up-fade" style="animation-delay: 0.1s;">
            <!-- Canlı Denetim Paneli -->
            <div class="col-lg-8">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between mb-4">
                        <h5 class="text-light fw-bold m-0"><span class="live-dot"></span><i class="fa-solid fa-tower-observation text-warning me-2"></i>Bugünün Canlı Akışı</h5>
                        <a href="appointments.php" class="btn btn-sm btn-outline-info rounded-pill">Tüm Randevular</a>
                    </div>
                    <?php if(count($hemen_randevular) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle" style="background: transparent;">
                            <thead>
                                <tr>
                                    <th class="text-muted">Saat</th>
                                    <th class="text-muted">Öğretmen</th>
                                    <th class="text-muted">Veli</th>
                                    <th class="text-muted">Durum</th>
                                    <th class="text-muted text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($hemen_randevular as $r): ?>
                                <tr>
                                    <td class="fw-bold text-info"><?php echo $r['saat']; ?></td>
                                    <td><?php echo htmlspecialchars($r['ogretmen_ad']); ?></td>
                                    <td><?php echo htmlspecialchars(($r['veli_ad'] ?? '') . ' ' . ($r['veli_soyad'] ?? '')); ?></td>
                                    <td>
                                        <?php if($r['durum'] == 'tamamlandi'): ?>
                                            <span class="badge bg-success">Tamamlandı</span>
                                        <?php elseif($r['durum'] == 'bekliyor' || $r['durum'] == 'onaylandi'): ?>
                                            <span class="badge bg-warning text-dark">Bekleniyor</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">İptal / Gelmedi</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if($r['durum'] != 'iptal' && $r['durum'] != 'tamamlandi'): ?>
                                        <a href="appointments.php?iptal_et=<?php echo $r['id']; ?>" class="btn btn-sm btn-danger rounded" onclick="return confirm('İdari izin nedeniyle iptal etmek istediğinize emin misiniz?');"><i class="fa-solid fa-ban"></i> İptal</a>
                                        <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>—</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-bed fs-1 text-muted mb-3"></i>
                            <p class="text-light">Bugün için bekleyen randevu bulunmuyor.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Hızlı Aksiyonlar + Branş İstatistikleri -->
            <div class="col-lg-4 d-flex flex-column gap-4">
                <div class="glass-card p-4">
                    <h5 class="text-light fw-bold mb-4"><i class="fa-solid fa-bolt text-warning me-2"></i>Hızlı İşlemler</h5>
                    <div class="d-flex flex-column gap-3">
                        <a href="teachers.php" class="quick-action-btn">
                            <i class="fa-solid fa-user-plus fs-4 mb-2 text-info"></i><br>
                            Yeni Öğretmen Ekle
                        </a>
                        <a href="announcements.php" class="quick-action-btn border-warning">
                            <i class="fa-solid fa-bell fs-4 mb-2 text-warning"></i><br>
                            Yeni Duyuru Yayınla
                        </a>
                        <a href="users.php?action=excel" class="quick-action-btn border-success">
                            <i class="fa-solid fa-file-excel fs-4 mb-2 text-success"></i><br>
                            Toplu E-Okul Excel Aktarımı
                        </a>
                        <a href="settings.php" class="quick-action-btn border-info">
                            <i class="fa-solid fa-clock fs-4 mb-2 text-info"></i><br>
                            Zil / Randevu Saatlerini Güncelle
                        </a>
                    </div>
                </div>

                <?php if(count($brans_istatistik) > 0): ?>
                <div class="glass-card p-4">
                    <h5 class="text-light fw-bold mb-3"><i class="fa-solid fa-chart-bar text-info me-2"></i>Branş İstatistikleri</h5>
                    <?php 
                    $max_randevu = $brans_istatistik[0]['randevu_sayisi'] ?? 1;
                    $renkler = ['#5BC0BE', '#f6c23e', '#e83e8c', '#28a745', '#6f42c1'];
                    foreach($brans_istatistik as $i => $bi): 
                        $yuzde = round(($bi['randevu_sayisi'] / $max_randevu) * 100);
                        $renk = $renkler[$i % count($renkler)];
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between" style="font-size: 0.85rem;">
                            <span class="text-light"><?php echo htmlspecialchars($bi['brans']); ?></span>
                            <span style="color: <?php echo $renk; ?>" class="fw-bold"><?php echo $bi['randevu_sayisi']; ?> görüşme</span>
                        </div>
                        <div class="brans-bar">
                            <div class="brans-bar-fill" style="width: <?php echo $yuzde; ?>%; background: <?php echo $renk; ?>;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../assets/js/app.js?v=<?php echo time(); ?>"></script>
    <script>
         else {
                icon.classList.remove('fa-sun'); icon.classList.add('fa-moon'); icon.style.color = '#fff';
            }
        }
    </script>
</body>
</html>
