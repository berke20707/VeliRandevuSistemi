<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Sadece öğrenci erişebilsin
if ($_SESSION['rol'] !== 'ogrenci') {
    header("Location: ../dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$ad_soyad = $_SESSION['ad_soyad'];
$okul_no = $_SESSION['okul_no'] ?? '';

$ogrenci_stmt = $db->prepare("SELECT * FROM ogrenciler WHERE id = ?");
$ogrenci_stmt->execute([$user_id]);
$ogrenci = $ogrenci_stmt->fetch(PDO::FETCH_ASSOC);
$sinif = $ogrenci['sinif'] ?? 'Bilinmiyor';

$gelen_duyurular = $db->query("SELECT * FROM announcements WHERE is_active = 1 AND hedef IN ('hepsi', 'ogrenci') ORDER BY created_at DESC LIMIT 5")->fetchAll();
$bildirim_sayisi = count($gelen_duyurular);

// MEB Duyuru çekme fonksiyonu
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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Paneli | Ahi Evran MTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        body { transition: background 0.5s ease, color 0.5s ease; }
        .news-card { position: relative; border-radius: 10px; overflow: hidden; border: 1px solid rgba(91, 192, 190, 0.3); display: block; transition: transform 0.3s; min-height: 160px; }
        .news-card:hover { transform: scale(1.02); z-index: 10; box-shadow: 0 5px 15px rgba(91, 192, 190, 0.3); }
        .news-img { width: 100%; height: 100%; object-fit: cover; transition: all 0.5s; position: absolute; top:0; left:0; }
        .news-card:hover .news-img { transform: scale(1.1); filter: brightness(0.7); }
        .news-overlay { position: absolute; bottom: 0; left: 0; width: 100%; padding: 15px; background: linear-gradient(to top, rgba(0,0,0,0.9), transparent); color: #fff; }
        .news-title { font-size: 0.9rem; font-weight: bold; margin: 0; text-shadow: 1px 1px 3px #000; }
        .progress-glass { background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); height: 12px; border-radius: 10px; overflow: hidden; }
        .student-welcome-card { background: linear-gradient(135deg, rgba(28, 37, 65, 0.9), rgba(91, 192, 190, 0.15)); border: 1px solid rgba(91, 192, 190, 0.3); border-radius: 20px; padding: 30px; position: relative; overflow: hidden; }
        .student-welcome-card::before { content: ''; position: absolute; top: -50%; right: -30%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(91,192,190,0.08) 0%, transparent 70%); border-radius: 50%; }
        .info-chip { display: inline-flex; align-items: center; gap: 6px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 30px; padding: 6px 14px; font-size: 0.8rem; color: #A0B2C6; }
        .info-chip i { color: var(--neon-blue); }
        .quick-link-card { background: rgba(28, 37, 65, 0.7); border: 1px solid rgba(91, 192, 190, 0.2); border-radius: 15px; padding: 20px; text-align: center; transition: all 0.3s; cursor: pointer; text-decoration: none; display: block; }
        .quick-link-card:hover { transform: translateY(-5px); border-color: var(--neon-blue); box-shadow: 0 10px 25px rgba(91, 192, 190, 0.2); }
        .quick-link-card .ql-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 1.3rem; }
        .glass-dropdown .dropdown-item:hover { background: rgba(91, 192, 190, 0.2) !important; color: #fff !important; }
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
        <a href="dashboard.php" class="sidebar-link" style="background: rgba(91, 192, 190, 0.15); border-right: 4px solid var(--neon-blue);">
            <i class="fa-solid fa-house"></i> <span class="sidebar-text">Ana Panel</span>
        </a>
        <a href="okulumuz.php" class="sidebar-link"><i class="fa-solid fa-school-flag"></i> <span class="sidebar-text">Okulumuz Hakkında</span></a>
        <a href="../ogretmenlerimiz.php" class="sidebar-link"><i class="fa-solid fa-users-viewfinder"></i> <span class="sidebar-text">Eğitim Kadrosu</span></a>
        <a href="../profil.php" class="sidebar-link"><i class="fa-solid fa-user-gear"></i> <span class="sidebar-text">Profilim</span></a>

        <div style="position: absolute; bottom: 20px; width: 100%;">
            <a href="../../logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket text-danger"></i> <span class="sidebar-text">Güvenli Çıkış</span></a>
        </div>
    </nav>

    <div class="main-content" style="padding: 30px 50px;">
        <!-- Üst bar -->
        <div class="row align-items-center mb-4">
            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
            </div>
            <div class="col-lg-6 col-md-12 d-flex justify-content-lg-end justify-content-start align-items-center">
                <div class="dropdown position-relative me-4 cursor-pointer">
                    <div data-bs-toggle="dropdown" aria-expanded="false" class="d-inline-block">
                        <i class="fa-solid fa-bell fs-4 text-light"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow-sm" style="font-size:0.7rem;"><?php echo $bildirim_sayisi; ?></span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg p-0 glass-dropdown" style="background: rgba(28, 37, 65, 0.95); border: 1px solid var(--neon-blue); border-radius: 12px; width: 320px; overflow: hidden; backdrop-filter: blur(10px); z-index: 9999;">
                        <li class="p-3" style="border-bottom: 1px solid rgba(91, 192, 190, 0.2);">
                            <h6 class="m-0 text-light fw-bold"><i class="fa-solid fa-bell text-warning me-2"></i>Okul Bildirimleri</h6>
                        </li>
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
                            <li><div class="p-3 text-center text-muted" style="font-size:0.85rem;">Yeni okul duyurusu bulunmuyor.</div></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <button class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; border-color: rgba(255,255,255,0.2); transition: all 0.3s;" onclick="toggleThemeMode()" title="Gündüz/Gece Modu">
                    <i id="theme-icon-indicator" class="fa-solid fa-moon text-light"></i>
                </button>
                <a href="../profil.php" class="text-decoration-none">
                    <div class="d-flex align-items-center p-1 rounded-pill shadow-sm" style="background: rgba(28, 37, 65, 0.9); border: 1px solid var(--neon-blue); padding-right: 15px !important; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.boxShadow='0 0 15px rgba(91,192,190,0.5)';" onmouseout="this.style.boxShadow='';">
                        <div class="rounded-circle bg-dark d-flex justify-content-center align-items-center me-2 overflow-hidden" style="width: 45px; height: 45px; border: 1px solid var(--neon-blue);">
                            <i class="fa-solid fa-user-graduate text-light" style="font-size: 1.2rem;"></i>
                        </div>
                        <div class="d-flex flex-column lh-1 pe-2">
                            <span class="text-light fw-bold" style="font-size: 0.95rem;"><?php echo htmlspecialchars($ad_soyad); ?></span>
                            <span style="font-size: 0.7rem; color: #A0B2C6;"><?php echo htmlspecialchars($sinif); ?></span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Hoş geldiniz kartı -->
        <div class="student-welcome-card mb-4 slide-up-fade">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="text-light fw-bold mb-2"><?php echo htmlspecialchars($ad_soyad); ?> Hoşgeldiniz.</h3>
                    <p style="color: #A0B2C6; font-size: 0.95rem; margin-bottom: 15px;">Bu panel üzerinden ders programınızı, öğretmenlerinizi ve okul duyurularınızı takip edebilirsiniz.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="info-chip"><i class="fa-solid fa-id-badge"></i> <?php echo htmlspecialchars($okul_no); ?></span>
                        <span class="info-chip"><i class="fa-solid fa-graduation-cap"></i> <?php echo htmlspecialchars($sinif); ?></span>
                        <span class="info-chip"><i class="fa-solid fa-calendar-day"></i> <?php echo date('d.m.Y'); ?></span>
                    </div>
                </div>
                <div class="col-lg-4 text-center d-none d-lg-block">
                    <i class="fa-solid fa-user-graduate" style="font-size: 5rem; color: rgba(91, 192, 190, 0.15);"></i>
                </div>
            </div>
        </div>

        <!-- Hızlı erişim kartları -->
        <div class="row g-4 mb-4 slide-up-fade" style="animation-delay: 0.1s;">
            <div class="col-lg-3 col-md-6">
                <a href="okulumuz.php" class="quick-link-card">
                    <div class="ql-icon" style="background: rgba(91, 192, 190, 0.15); color: #5BC0BE;"><i class="fa-solid fa-school-flag"></i></div>
                    <h6 class="text-light fw-bold m-0 mb-1">Okulumuz Hakkında</h6>
                    <p class="m-0" style="font-size: 0.75rem; color: #A0B2C6;">Tarihçe, vizyon ve misyon</p>
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="../ogretmenlerimiz.php" class="quick-link-card">
                    <div class="ql-icon" style="background: rgba(40, 167, 69, 0.15); color: #28a745;"><i class="fa-solid fa-users-viewfinder"></i></div>
                    <h6 class="text-light fw-bold m-0 mb-1">Eğitim Kadrosu</h6>
                    <p class="m-0" style="font-size: 0.75rem; color: #A0B2C6;">Öğretmen ve idare bilgileri</p>
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="../profil.php" class="quick-link-card">
                    <div class="ql-icon" style="background: rgba(246, 194, 62, 0.15); color: #f6c23e;"><i class="fa-solid fa-user-gear"></i></div>
                    <h6 class="text-light fw-bold m-0 mb-1">Profilim</h6>
                    <p class="m-0" style="font-size: 0.75rem; color: #A0B2C6;">Hesap ayarları ve bilgiler</p>
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="https://e-okul.meb.gov.tr" target="_blank" class="quick-link-card">
                    <div class="ql-icon" style="background: rgba(220, 53, 69, 0.15); color: #dc3545;"><i class="fa-solid fa-graduation-cap"></i></div>
                    <h6 class="text-light fw-bold m-0 mb-1">E-Okul</h6>
                    <p class="m-0" style="font-size: 0.75rem; color: #A0B2C6;">Not ve devamsızlık bilgileri</p>
                </a>
            </div>
        </div>

        <div class="row g-4 mb-4 slide-up-fade" style="animation-delay: 0.2s;">
            <!-- Sınıf Öğretmeni Bilgisi (Yarı alan kaplar) -->
            <div class="col-lg-6">
                <div class="glass-card p-4 d-flex align-items-center h-100" style="border-left: 4px solid var(--neon-blue);">
                    <div class="position-relative me-3">
                        <img src="https://ui-avatars.com/api/?name=Erol+Altekin&background=0B132B&color=5BC0BE&rounded=true&size=60" class="rounded-circle border border-info">
                        <i class="fa-solid fa-crown text-warning position-absolute" style="top: -5px; right: -5px; font-size: 1rem; text-shadow: 0 0 5px #f6c23e;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="text-light fw-bold m-0">Erol ALTEKİN</h5>
                        <p class="m-0" style="font-size: 0.75rem; color: #A0B2C6;">AMP <?php echo htmlspecialchars($sinif); ?> Sınıf Öğretmeni</p>
                    </div>
                    <a href="../ogretmenlerimiz.php" class="btn btn-neon px-3 py-2" title="Eğitim Kadrosu"><i class="fa-solid fa-users"></i></a>
                </div>
            </div>

            <!-- E-Okul Hızlı Giriş (Diğer Yarı alanı kaplar) -->
            <div class="col-lg-6">
                <div class="glass-card p-4 text-center h-100 d-flex flex-column justify-content-center align-items-center" style="background: linear-gradient(135deg, rgba(28,37,65,0.8), rgba(91,192,190,0.1)); border: 1px solid rgba(91,192,190,0.3);">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="fa-solid fa-graduation-cap fs-2 text-info me-3"></i>
                        <h5 class="text-light fw-bold m-0">E-Okul Öğrenci Girişi</h5>
                    </div>
                    <p style="font-size: 0.8rem; color: #A0B2C6; margin-bottom: 15px;">Not, devamsızlık ve sınav bilgilerinizi T.C. MEB platformundan inceleyin.</p>
                    <a href="https://e-okul.meb.gov.tr" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-4 fw-bold">Sisteme Git <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="row g-4 slide-up-fade" style="animation-delay: 0.3s;">
            <div class="col-lg-12">
                <!-- Okuldan Haberler -->
                <div class="glass-card p-4 d-flex flex-column flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-4"><h5 class="text-light m-0 fw-bold"><i class="fa-solid fa-globe neon-text me-2"></i> Okuldan Haberler</h5><span class="badge" style="background: rgba(91, 192, 190, 0.1); color: var(--neon-blue); border: 1px solid var(--neon-blue); padding: 5px 10px;">Canlı</span></div>
                    <div class="row g-3">
                        <?php foreach($canli_duyurular as $haber): ?>
                            <div class="col-md-6">
                                <a href="<?php echo htmlspecialchars($haber['link']); ?>" target="_blank" class="news-card flex-grow-1 h-100"><img src="<?php echo htmlspecialchars($haber['resim']); ?>" class="news-img" alt="Haber"><div class="news-overlay"><p class="news-title"><?php echo htmlspecialchars($haber['baslik']); ?></p></div></a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>

    <!-- Duyuru Okuma Modalı -->
    <div class="modal fade" id="duyuruModal" tabindex="-1" style="z-index: 100000;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#111827; color:#fff; border:1px solid var(--neon-blue); box-shadow: 0 0 30px rgba(91,192,190,0.2);">
          <div class="modal-header border-0 border-bottom border-secondary">
            <h5 class="modal-title text-warning"><i class="fa-solid fa-bullhorn me-2"></i>Okul Duyurusu</h5>
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
    <script src="../../assets/js/app.js?v=<?php echo time(); ?>"></script>
    <script>
        function duyuruAc(baslik, icerik, tarih) {
            document.getElementById('duyuruBaslik').innerText = baslik;
            document.getElementById('duyuruIcerik').innerHTML = icerik;
            document.getElementById('duyuruTarih').innerHTML = '<i class="fa-regular fa-clock"></i> ' + tarih;
            new bootstrap.Modal(document.getElementById('duyuruModal')).show();
        }

         else {
                icon.classList.remove('fa-sun'); icon.classList.add('fa-moon'); icon.style.color = '#fff';
            }
        }
    </script>
</body>
</html>
