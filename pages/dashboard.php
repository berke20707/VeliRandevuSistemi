<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$ad_soyad = $_SESSION['ad_soyad'];
$rol = $_SESSION['rol'];

if ($rol === 'ogrenci') {
    header("Location: student/dashboard.php");
    exit;
}

$aktif_ogrenci_ad = $_SESSION['aktif_ogrenci_ad'] ?? "Öğrenci";

$gelen_duyurular = $db->query("SELECT * FROM announcements WHERE is_active = 1 AND hedef IN ('hepsi', '$rol') ORDER BY created_at DESC LIMIT 5")->fetchAll();
$bildirim_sayisi = count($gelen_duyurular);

$profil_stmt = $db->prepare("SELECT profil_resmi FROM users WHERE id = ?");
$profil_stmt->execute([$user_id]);
$aktif_kullanici = $profil_stmt->fetch();
$profil_resmi = $aktif_kullanici['profil_resmi'] ? $aktif_kullanici['profil_resmi'] : 'fa-user';
$resim_html = strpos($profil_resmi, 'fa-') === 0 
    ? "<i class='fa-solid $profil_resmi text-light' style='font-size: 1.2rem;'></i>" 
    : "<img src='../assets/img/$profil_resmi' alt='Profil' style='width: 100%; height: 100%; object-fit: cover;'>";

function getMebResimliDuyurular() {
    $url = "https://corluahievranmtal.meb.k12.tr";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $html = curl_exec($ch);
    curl_close($ch);

    $yedek_duyurular = [
        ['link' => '#', 'resim' => 'https://corluahievranmtal.meb.k12.tr/meb_iys_dosyalar/59/03/964344/resimler/2024_01/k_05101010_zanaat.jpg', 'baslik' => 'Okulumuzda Zanaat Atölyeleri Açılmıştır!'],
        ['link' => '#', 'resim' => 'https://corluahievranmtal.meb.k12.tr/meb_iys_dosyalar/59/03/964344/resimler/2024_01/k_05101010_sinav.jpg', 'baslik' => 'Şubat Sorumluluk Sınavları']
    ];

    if(!$html) return $yedek_duyurular; 

    $dom = new DOMDocument();
    @$dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $html);
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//a[contains(@href, 'icerik') and .//img]"); 
    
    $duyurular = []; $sayac = 0;
    foreach ($nodes as $node) {
        if($sayac >= 2) break; 
        $link = $node->getAttribute('href');
        $img = $node->getElementsByTagName('img')->item(0);
        if($img) {
            $img_src = $img->getAttribute('src');
            $baslik = trim($img->getAttribute('alt')) ?: 'Okuldan Yeni Haber';
            $tam_link = strpos($link, 'http') === 0 ? $link : $url . "/" . ltrim($link, '/');
            $tam_img = strpos($img_src, 'http') === 0 ? $img_src : $url . "/" . ltrim($img_src, '/');
            $duyurular[] = ['link' => $tam_link, 'resim' => $tam_img, 'baslik' => $baslik];
            $sayac++;
        }
    }
    return count($duyurular) > 0 ? $duyurular : $yedek_duyurular;
}
$canli_duyurular = getMebResimliDuyurular();

// Dashboard İstatistikleri
$bugun_tarih = date('Y-m-d');
$yaklasan_stmt = $db->prepare("SELECT COUNT(*) as sayi FROM randevular WHERE veli_id = ? AND durum IN ('bekliyor', 'onaylandi') AND tarih > ?");
$yaklasan_stmt->execute([$user_id, $bugun_tarih]);
$yaklasan_sayi = $yaklasan_stmt->fetch()['sayi'];

$tamamlanan_stmt = $db->prepare("SELECT COUNT(*) as sayi FROM randevular WHERE veli_id = ? AND durum = 'tamamlandi'");
$tamamlanan_stmt->execute([$user_id]);
$tamamlanan_sayi = $tamamlanan_stmt->fetch()['sayi'];

$son_ziy_stmt = $db->prepare("SELECT tarih FROM randevular WHERE veli_id = ? AND durum = 'tamamlandi' ORDER BY tarih DESC LIMIT 1");
$son_ziy_stmt->execute([$user_id]);
$son_ziy = $son_ziy_stmt->fetch();

$aylar_tr = ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"];
if ($son_ziy) {
    $t_parca = explode('-', $son_ziy['tarih']);
    $son_ziyaret_str = ltrim($t_parca[2], '0') . " " . $aylar_tr[(int)$t_parca[1] - 1] . " " . $t_parca[0];
} else {
    $son_ziyaret_str = "Henüz Yok";
}

$tum_randevu_stmt = $db->prepare("SELECT * FROM randevular WHERE veli_id = ? AND durum IN ('bekliyor', 'onaylandi')");
$tum_randevu_stmt->execute([$user_id]);
$randevular_json = json_encode($tum_randevu_stmt->fetchAll()); 
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli | Ahi Evran MTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=37">
    <style>
        .calendar-container { background: rgba(28, 37, 65, 0.6); border-radius: 15px; padding: 25px; border: 1px solid rgba(91, 192, 190, 0.2); }
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; color: var(--neon-blue); font-weight: bold; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; text-align: center; }
        .day-name { color: #A0B2C6; font-size: 0.85rem; font-weight: bold; margin-bottom: 5px; }
        .day-box { background: rgba(0,0,0,0.3); padding: 12px; border-radius: 10px; color: #E6F1F9; cursor: pointer; transition: all 0.2s; position: relative; border: 1px solid transparent; font-size: 1rem; font-weight: 500;}
        .day-box:hover:not(.holiday) { background: rgba(255,255,255,0.1); transform: scale(1.1); z-index: 5; }
        .day-box.empty { visibility: hidden; }
        .day-box.holiday { background: rgba(0,0,0,0.8); border: 1px solid rgba(255,255,255,0.02); color: #444; cursor: not-allowed; text-decoration: line-through; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 4px; }
        .news-card { position: relative; border-radius: 10px; overflow: hidden; border: 1px solid rgba(91, 192, 190, 0.3); display: block; transition: transform 0.3s; min-height: 160px; }
        .news-card:hover { transform: scale(1.02); z-index: 10; box-shadow: 0 5px 15px rgba(91, 192, 190, 0.3); }
        .news-img { width: 100%; height: 100%; object-fit: cover; transition: all 0.5s; position: absolute; top:0; left:0; }
        .news-card:hover .news-img { transform: scale(1.1); filter: brightness(0.7); }
        .news-overlay { position: absolute; bottom: 0; left: 0; width: 100%; padding: 15px; background: linear-gradient(to top, rgba(0,0,0,0.9), transparent); color: #fff; }
        .news-title { font-size: 0.9rem; font-weight: bold; margin: 0; text-shadow: 1px 1px 3px #000; }
        .progress-glass { background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); height: 12px; border-radius: 10px; overflow: hidden; }
        /* Arama barı CSS'i kaldırıldı */
        .glass-dropdown .dropdown-item:hover { background: rgba(91, 192, 190, 0.2) !important; color: #fff !important; }
    </style>
</head>
<body class="bg-dark-space">
    <div class="school-watermark"><i class="fa-solid fa-graduation-cap"></i></div>
    <div id="particles-js" style="position: fixed; z-index: -1;"></div>

    <nav class="glass-sidebar">
        <div class="text-center mb-5 mt-3 px-2 text-light fw-bold" style="border-bottom: 1px solid rgba(91, 192, 190, 0.2); padding-bottom: 20px;">
            <img src="../assets/img/logo.png" alt="Ahi Evran MTAL Logosu" class="sidebar-logo mb-3">
            <br>
            <span class="sidebar-text" style="font-size: 0.9rem; line-height: 1.5; display: block;">
                Ahi Evran Mesleki Ve Teknik<br>Anadolu Lisesi<br>
                <span class="neon-text" style="font-size: 0.75rem; font-weight: 400;">Veli Randevu Sistemi</span>
            </span>
        </div>
        <a href="dashboard.php" class="sidebar-link" style="background: rgba(91, 192, 190, 0.15); border-right: 4px solid var(--neon-blue);">
            <i class="fa-solid fa-house"></i> <span class="sidebar-text">Ana Panel</span>
        </a>
        <a href="randevu_al.php" class="sidebar-link"><i class="fa-solid fa-calendar-plus"></i> <span class="sidebar-text">Randevu Al</span></a>
        <a href="ogretmenlerimiz.php" class="sidebar-link"><i class="fa-solid fa-users-viewfinder"></i> <span class="sidebar-text">Eğitim Kadrosu</span></a>
        <a href="profil.php" class="sidebar-link"><i class="fa-solid fa-user-gear"></i> <span class="sidebar-text">Profilim</span></a>

        <div style="position: absolute; bottom: 20px; width: 100%;">
            <a href="../logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket text-danger"></i> <span class="sidebar-text">Güvenli Çıkış</span></a>
        </div>
    </nav>

    <div class="main-content" style="padding: 30px 50px;">
        <div class="row align-items-center mb-4">
            <div class="col-lg-4 col-md-12 mb-3 mb-lg-0">
                <h4 class="text-light fw-bold m-0" style="font-size: 1.4rem;">Hoş geldiniz <span class="neon-text"><?php echo htmlspecialchars($ad_soyad); ?></span>,</h4>
                <p class="m-0 mt-1" style="color: #A0B2C6; font-size: 1rem;"><b class="text-warning"><?php echo htmlspecialchars($aktif_ogrenci_ad); ?></b> için işlem yapmaktasınız.</p>
            </div>
            <div class="col-lg-4 col-md-12 mb-3 mb-lg-0 d-flex justify-content-center">
                <!-- Boş alan: İsteğe göre kaldırıldı -->
            </div>
            <div class="col-lg-4 col-md-12 d-flex justify-content-lg-end justify-content-start align-items-center">
                <div class="dropdown position-relative me-4 cursor-pointer">
                    <div data-bs-toggle="dropdown" aria-expanded="false" class="d-inline-block">
                        <i class="fa-solid fa-bell fs-4 text-light"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow-sm" style="font-size:0.7rem;"><?php echo $bildirim_sayisi; ?></span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg p-0 glass-dropdown" style="background: rgba(28, 37, 65, 0.95); border: 1px solid var(--neon-blue); border-radius: 12px; width: 320px; overflow: hidden; backdrop-filter: blur(10px);">
                        <li class="p-3" style="border-bottom: 1px solid rgba(91, 192, 190, 0.2);">
                            <h6 class="m-0 text-light fw-bold"><i class="fa-solid fa-bell text-warning me-2"></i>Bildirimler</h6>
                        </li>
                        <!-- Okul Müdürü Yönetimi bildirimi gizlendi -->
                        <?php if($bildirim_sayisi > 0): ?>
                            <?php foreach($gelen_duyurular as $d): ?>
                            <li><a class="dropdown-item p-3 text-light" href="#" onclick="duyuruAc('<?php echo htmlspecialchars(addslashes($d['title'])); ?>', '<?php echo htmlspecialchars(addslashes(preg_replace('/\r|\n/', '<br>', $d['content']))); ?>', '<?php echo date('d.m.Y H:i', strtotime($d['created_at'])); ?>')" style="white-space: normal; transition: 0.3s; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <div class="d-flex align-items-start">
                                    <i class="fa-solid fa-bullhorn text-warning mt-1 me-3 fs-5"></i>
                                    <div>
                                        <strong class="d-block text-warning" style="font-size: 0.9rem;"><?php echo htmlspecialchars($d['title']); ?></strong>
                                        <span style="font-size: 0.8rem; color: #A0B2C6; display: block; margin-top:3px;"><?php echo mb_strimwidth(htmlspecialchars($d['content']), 0, 40, "..."); ?></span>
                                    </div>
                                </div>
                            </a></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><div class="p-3 text-center text-muted" style="font-size:0.85rem;">Yeni bildiriminiz yok.</div></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; border-color: rgba(255,255,255,0.2); transition: all 0.3s;" onclick="toggleThemeMode()" title="Gündüz/Gece Modu">
                        <i id="theme-icon-indicator" class="fa-solid fa-moon text-light"></i>
                    </button>
                </div>
                <a href="profil.php" class="text-decoration-none">
                    <div class="d-flex align-items-center p-1 rounded-pill shadow-sm" style="background: rgba(28, 37, 65, 0.9); border: 1px solid var(--neon-blue); padding-right: 15px !important;">
                        <div class="rounded-circle bg-dark d-flex justify-content-center align-items-center me-2 overflow-hidden" style="width: 45px; height: 45px; border: 1px solid var(--neon-blue);">
                            <?php echo $resim_html; ?>
                        </div>
                        <div class="d-flex flex-column lh-1 pe-2">
                            <span class="text-light fw-bold" style="font-size: 0.95rem;"><?php echo htmlspecialchars($ad_soyad); ?></span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row mb-4" id="iptalUyariKutusu" style="display:none;">
            <div class="col-12">
                <div class="alert mb-0 d-flex align-items-center py-3" style="background: rgba(220, 53, 69, 0.15); border: 1px solid #dc3545; color: #ff6b6b; border-radius: 10px;">
                    <i class="fa-solid fa-triangle-exclamation fs-4 me-3 fa-fade"></i>
                    <div>
                        <strong class="fw-bold" style="font-size: 1rem;">Geçmiş Randevu İptali!</strong>
                        <p class="m-0" style="font-size: 0.85rem; color: #E6F1F9;">Geçmişe dönük bir randevunuz iptal edilmiştir. Yeni randevu alabilirsiniz.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-auto py-2" data-bs-dismiss="alert" onclick="kapatUyari()"></button>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6"><div class="glass-card p-4 d-flex align-items-center justify-content-between h-100"><div><p class="text-light m-0 mb-1" style="font-size: 0.95rem;">Yaklaşan Randevu</p><h2 class="fw-bold text-success m-0"><?php echo $yaklasan_sayi; ?></h2></div><div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(40,167,69,0.2); color: #28a745; font-size:1.2rem;"><i class="fa-solid fa-calendar-check"></i></div></div></div>
            <div class="col-lg-3 col-md-6"><div class="glass-card p-4 d-flex align-items-center justify-content-between h-100"><div><p class="text-light m-0 mb-1" style="font-size: 0.95rem;">Tamamlanan Görüşme</p><h2 class="fw-bold text-warning m-0"><?php echo $tamamlanan_sayi; ?></h2></div><div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(246,194,62,0.2); color: #f6c23e; font-size:1.2rem;"><i class="fa-solid fa-handshake"></i></div></div></div>
            <div class="col-lg-3 col-md-6"><div class="glass-card p-4 d-flex align-items-center justify-content-between h-100"><div><p class="text-light m-0 mb-1" style="font-size: 0.95rem;">Son Okul Ziyareti</p><h4 class="fw-bold text-info m-0"><?php echo $son_ziyaret_str; ?></h4></div><div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(54,185,204,0.2); color: #36b9cc; font-size:1.2rem;"><i class="fa-solid fa-school"></i></div></div></div>
            <div class="col-lg-3 col-md-6"><div class="glass-card p-4 d-flex align-items-center h-100" style="border-left: 4px solid var(--neon-blue);"><div class="position-relative me-3"><img src="https://ui-avatars.com/api/?name=Erol+Altekin&background=0B132B&color=5BC0BE&rounded=true&size=60" class="rounded-circle border border-info"><i class="fa-solid fa-crown text-warning position-absolute" style="top: -5px; right: -5px; font-size: 1rem; text-shadow: 0 0 5px #f6c23e;"></i></div><div class="flex-grow-1"><h5 class="text-light fw-bold m-0">Erol ALTEKİN</h5><p class="m-0" style="font-size: 0.75rem; color: #A0B2C6;">AMP 11/B Sınıf Öğretmeni</p></div><a href="randevu_al.php" class="btn btn-neon px-3 py-2" title="Hemen Randevu Al"><i class="fa-solid fa-calendar-plus"></i></a></div></div>
        </div>

        <div class="row g-4 d-flex align-items-stretch">
            <div class="col-lg-8 d-flex flex-column gap-4">
                <div class="glass-card p-4 d-flex flex-column justify-content-center">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0" style="border-right: 1px solid rgba(255,255,255,0.1);">
                            <div class="d-flex align-items-center mb-2"><i class="fa-solid fa-triangle-exclamation text-warning fs-4 me-2 fa-fade"></i><h6 class="text-light m-0 fw-bold">Özürsüz Devamsızlık</h6></div>
                            <div class="d-flex justify-content-between mb-1" style="font-size: 0.8rem; color: #E6F1F9;"><span>Kullanılan: <b class="text-warning">9,5 Gün</b></span><span class="text-warning fw-bold">DİKKAT (Sınır: 10)</span></div>
                            <div class="progress progress-glass"><div class="progress-bar bg-warning" style="width: 95%; box-shadow: 0 0 10px #ffc107;"></div></div>
                        </div>
                        <div class="col-md-6 ps-md-4">
                            <div class="d-flex align-items-center mb-2"><i class="fa-solid fa-file-medical text-danger fs-4 me-2"></i><h6 class="text-light m-0 fw-bold">Özürlü (İzin/Rapor)</h6></div>
                            <div class="d-flex justify-content-between mb-1" style="font-size: 0.8rem; color: #E6F1F9;"><span>Kullanılan: <b class="text-danger">19,5 Gün</b></span><span class="text-danger fw-bold">RİSKLİ (Sınır: 20)</span></div>
                            <div class="progress progress-glass"><div class="progress-bar bg-danger" style="width: 97.5%; box-shadow: 0 0 10px #dc3545;"></div></div>
                        </div>
                    </div>
                </div>
                <div class="calendar-container flex-grow-1">
                    <div class="calendar-header mb-3"><i class="fa-solid fa-chevron-left cursor-pointer fs-4" onclick="AyiDegistir(-1)"></i><span id="takvimAyi" class="fs-5">Mart 2026</span><i class="fa-solid fa-chevron-right cursor-pointer fs-4" onclick="AyiDegistir(1)"></i></div>
                    <div class="d-flex justify-content-center flex-wrap gap-3 mb-3 pb-3" style="font-size: 0.85rem; color: #A0B2C6; border-bottom: 1px solid rgba(255,255,255,0.05);"><span><span class="legend-dot" style="background: rgba(220, 53, 69, 0.8); box-shadow: 0 0 5px #dc3545;"></span> Randevunuz</span><span><span class="legend-dot" style="background: #5BC0BE; box-shadow: 0 0 5px #5BC0BE;"></span> Müsait</span><span><span class="legend-dot" style="background: rgba(0,0,0,0.8); border: 1px solid rgba(255,255,255,0.2);"></span> Kapalı</span></div>
                    <div class="calendar-grid mb-2"><div class="day-name">Pzt</div><div class="day-name">Sal</div><div class="day-name">Çar</div><div class="day-name">Per</div><div class="day-name">Cum</div><div class="day-name text-danger">Cmt</div><div class="day-name text-danger">Paz</div></div>
                    <div class="calendar-grid" id="takvimGunleri"></div>
                </div>
            </div>
            <div class="col-lg-4 d-flex flex-column gap-4">
                <div class="glass-card p-4 d-flex flex-column flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-4"><h5 class="text-light m-0 fw-bold"><i class="fa-solid fa-globe neon-text me-2"></i> Okuldan Haberler</h5><span class="badge" style="background: rgba(91, 192, 190, 0.1); color: var(--neon-blue); border: 1px solid var(--neon-blue); padding: 5px 10px;">Canlı</span></div>
                    <div class="d-flex flex-column gap-3 flex-grow-1">
                        <?php foreach($canli_duyurular as $haber): ?>
                            <a href="<?php echo htmlspecialchars($haber['link']); ?>" target="_blank" class="news-card flex-grow-1"><img src="<?php echo htmlspecialchars($haber['resim']); ?>" class="news-img" alt="Haber"><div class="news-overlay"><p class="news-title"><?php echo htmlspecialchars($haber['baslik']); ?></p></div></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="glass-card p-4 text-center" style="background: linear-gradient(135deg, rgba(28,37,65,0.8), rgba(91,192,190,0.1)); border: 1px solid rgba(91,192,190,0.3);">
                    <i class="fa-solid fa-graduation-cap fs-2 text-info mb-3"></i>
                    <h6 class="text-light fw-bold">E-Okul Veli Bilgilendirme Sistemi</h6>
                    <p style="font-size: 0.8rem; color: #A0B2C6; margin-bottom: 12px;">Öğrencinizin not, devamsızlık ve sınav bilgilerini T.C. MEB platformundan inceleyin.</p>
                    <a href="https://e-okul.meb.gov.tr" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-4 fw-bold">Sisteme Git <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Duyuru Okuma Modalı -->
    <div class="modal fade" id="duyuruModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#111827; color:#fff; border:1px solid var(--neon-blue); box-shadow: 0 0 30px rgba(91,192,190,0.2);">
          <div class="modal-header border-0 border-bottom border-secondary">
            <h5 class="modal-title text-warning"><i class="fa-solid fa-bullhorn me-2"></i>Sistem Duyurusu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body border-0 p-4">
            <h4 id="duyuruBaslik" class="text-info fw-bold mb-3"></h4>
            <div id="duyuruIcerik" class="text-light" style="font-size: 0.95rem; line-height: 1.6;"></div>
            <small id="duyuruTarih" class="text-muted d-block mt-4"><i class="fa-regular fa-clock"></i> </small>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Kapat</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="../assets/js/app.js?v=<?php echo time(); ?>"></script>
    <script>
        function duyuruAc(baslik, icerik, tarih) {
            document.getElementById('duyuruBaslik').innerText = baslik;
            document.getElementById('duyuruIcerik').innerHTML = icerik;
            document.getElementById('duyuruTarih').innerHTML = '<i class="fa-regular fa-clock"></i> ' + tarih;
            new bootstrap.Modal(document.getElementById('duyuruModal')).show();
        }

        if (localStorage.getItem('iptalUyariKapali') !== 'true') { document.getElementById('iptalUyariKutusu').style.display = 'flex'; }
        function kapatUyari() { localStorage.setItem('iptalUyariKapali', 'true'); }
        // hocaSorgula() kaldırıldı
        const randevular = <?php echo $randevular_json; ?>;
        const aylar = ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"];
        const resmiTatiller = ["01-01", "04-23", "05-01", "05-19", "07-15", "08-30", "10-29"];
        let suAnkiTarih = new Date(); let gosterilenAy = suAnkiTarih.getMonth(); let gosterilenYil = suAnkiTarih.getFullYear();
        function TakvimOlustur(ay, yil) {
            const ilkGun = new Date(yil, ay, 1).getDay() || 7; const aydakiGunSayisi = new Date(yil, ay + 1, 0).getDate(); const bugun = new Date();
            document.getElementById("takvimAyi").innerText = `${aylar[ay]} ${yil}`; let html = '';
            for (let i = 1; i < ilkGun; i++) { html += `<div class="day-box empty"></div>`; }
            for (let i = 1; i <= aydakiGunSayisi; i++) {
                let currentStr = `${yil}-${String(ay+1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                let ayGunStr = `${String(ay+1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                let gunObj = new Date(yil, ay, i); let gunHaftaninKacinciGunu = gunObj.getDay(); 
                let isHaftaSonu = (gunHaftaninKacinciGunu === 0 || gunHaftaninKacinciGunu === 6);
                let isResmiTatil = resmiTatiller.includes(ayGunStr);
                let randevuTarihi = new Date(currentStr); let farkGun = Math.ceil((randevuTarihi - bugun) / (1000 * 60 * 60 * 24)); let isGecmis = farkGun <= 0;
                if (isHaftaSonu || isResmiTatil) { html += `<div class="day-box holiday" title="Tatil - Kapalı">${i}</div>`; continue; }
                let cssClass = ""; let titleAttr = ""; let varMi = randevular.find(r => r.tarih === currentStr);
                if(varMi) {
                    cssClass = "has-appt"; titleAttr = `title="Randevu: ${varMi.ogretmen_ad} - ${varMi.saat}"`;
                    if(isGecmis) { cssClass += " opacity-50"; } else if(farkGun === 0) { cssClass += " appt-today"; } else if(farkGun <= 1) { cssClass += " appt-close"; } else if(farkGun <= 3) { cssClass += " appt-medium"; } else { cssClass += " appt-far"; } 
                } else if (isGecmis) { cssClass += " opacity-50 holiday"; } else { titleAttr = `title="Müsait - Tıkla Randevu Al"`; }
                html += `<div class="day-box ${cssClass}" ${titleAttr} onclick="gunSec('${currentStr}', ${isGecmis})">${i}</div>`;
            }
            document.getElementById("takvimGunleri").innerHTML = html;
        }
        function AyiDegistir(yon) {
            gosterilenAy += yon; if (gosterilenAy < 0) { gosterilenAy = 11; gosterilenYil--; } else if (gosterilenAy > 11) { gosterilenAy = 0; gosterilenYil++; } TakvimOlustur(gosterilenAy, gosterilenYil);
        }
        function gunSec(tarih, isGecmis) { if(isGecmis) return; window.location.href = "randevu_al.php?tarih=" + tarih + "&otomatik_ac=1"; }
        TakvimOlustur(gosterilenAy, gosterilenYil);
    </script>
</body>
</html>