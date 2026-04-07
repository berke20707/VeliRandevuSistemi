<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

// Sadece öğretmenler erişebilir
if ($_SESSION['rol'] !== 'ogretmen') {
    header("Location: ../dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$ad_soyad = $_SESSION['ad_soyad'];
$brans = $_SESSION['brans'] ?? 'Öğretmen';

// Profil resmini çek
$profil_stmt = $db->prepare("SELECT profil_resmi, brans FROM users WHERE id = ?");
$profil_stmt->execute([$user_id]);
$aktif_kullanici = $profil_stmt->fetch();
$profil_resmi = $aktif_kullanici['profil_resmi'] ? $aktif_kullanici['profil_resmi'] : 'fa-chalkboard-user';
$resim_html = strpos($profil_resmi, 'fa-') === 0 
    ? "<i class='fa-solid $profil_resmi text-light' style='font-size: 1.2rem;'></i>" 
    : "<img src='../../assets/img/$profil_resmi' alt='Profil' style='width: 100%; height: 100%; object-fit: cover;'>";

$bugun = date('Y-m-d');

// --- İSTATİSTİKLER ---
// Bugünün Randevuları (Onaylanmış veya Tamamlanmış)
$bugun_randevu_stmt = $db->prepare("SELECT COUNT(*) FROM randevular WHERE ogretmen_ad = ? AND tarih = ? AND durum IN ('onaylandi', 'tamamlandi')");
$bugun_randevu_stmt->execute([$ad_soyad, $bugun]);
$bugun_gorusme_sayisi = $bugun_randevu_stmt->fetchColumn();

// Bekleyen Onaylar Sayısı
$bekleyen_sayi_stmt = $db->prepare("SELECT COUNT(*) FROM randevular WHERE ogretmen_ad = ? AND durum = 'bekliyor'");
$bekleyen_sayi_stmt->execute([$ad_soyad]);
$bekleyen_onay_sayisi = $bekleyen_sayi_stmt->fetchColumn();

// Toplam Onaylanan/Gelecek Randevu (Yaklaşan)
$gelecek_sayi_stmt = $db->prepare("SELECT COUNT(*) FROM randevular WHERE ogretmen_ad = ? AND durum = 'onaylandi' AND tarih >= ?");
$gelecek_sayi_stmt->execute([$ad_soyad, $bugun]);
$gelecek_randevu_sayisi = $gelecek_sayi_stmt->fetchColumn();

// Bekleyen Randevular Listesi (Detaylı)
$bekleyen_liste_stmt = $db->prepare("
    SELECT r.*, u.telefon 
    FROM randevular r 
    LEFT JOIN users u ON r.veli_id = u.id 
    WHERE ogretmen_ad = ? AND durum = 'bekliyor' 
    ORDER BY r.tarih ASC, r.saat ASC
");
$bekleyen_liste_stmt->execute([$ad_soyad]);
$bekleyen_randevular = $bekleyen_liste_stmt->fetchAll();

// Bugünkü Onaylanmış Randevular (Canlı Akış)
$bugunku_liste_stmt = $db->prepare("
    SELECT r.*, u.telefon 
    FROM randevular r 
    LEFT JOIN users u ON r.veli_id = u.id 
    WHERE ogretmen_ad = ? AND tarih = ? AND durum = 'onaylandi' 
    ORDER BY r.saat ASC
");
$bugunku_liste_stmt->execute([$ad_soyad, $bugun]);
$bugunku_randevular = $bugunku_liste_stmt->fetchAll();

// Son Eklenen İdari Duyuru
$son_duyuru = $db->query("SELECT * FROM announcements WHERE is_active = 1 AND hedef IN ('hepsi', 'ogretmen') ORDER BY created_at DESC LIMIT 1")->fetch();

// Bildirim Sayısı
$gelen_duyurular = $db->query("SELECT * FROM announcements WHERE is_active = 1 AND hedef IN ('hepsi', 'ogretmen') ORDER BY created_at DESC LIMIT 5")->fetchAll();
$bildirim_sayisi = count($gelen_duyurular);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğretmen Paneli | Ahi Evran MTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .teacher-stat-card { background: rgba(0,0,0,0.4); border: 1px solid rgba(91,192,190,0.3); border-radius: 15px; padding: 20px; transition: all 0.3s; position: relative; overflow: hidden; height: 100%;}
        .teacher-stat-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(91,192,190,0.2); }
        .teacher-stat-card .icon-box { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 15px; position: absolute; right: 20px; top: 20px; }
        
        .req-card { background: rgba(28, 37, 65, 0.7); border: 1px solid rgba(246, 194, 62, 0.3); border-radius: 12px; padding: 15px; transition: 0.3s; margin-bottom: 15px; border-left: 4px solid #f6c23e;}
        .req-card:hover { transform: translateX(5px); box-shadow: -5px 0 15px rgba(246, 194, 62, 0.1); }
        .req-card-today { border-color: rgba(40, 167, 69, 0.3); border-left: 4px solid #28a745; background: linear-gradient(to right, rgba(40, 167, 69, 0.05), transparent);}
        
        .timeline-box { position: relative; padding-left: 30px; margin-bottom: 20px; }
        .timeline-box::before { content: ''; position: absolute; left: 0; top: 0; bottom: -20px; width: 2px; background: rgba(255,255,255,0.1); }
        .timeline-box .timeline-dot { position: absolute; left: -6px; top: 0; width: 14px; height: 14px; border-radius: 50%; background: var(--neon-blue); box-shadow: 0 0 10px var(--neon-blue); }
        
        body.light-mode .req-card { background: rgba(255,255,255,0.9); border-color: rgba(0,0,0,0.1); }
        body.light-mode .teacher-stat-card { background: rgba(255,255,255,0.9); border-color: rgba(0,0,0,0.1); }
        .glass-dropdown .dropdown-item:hover { background: rgba(91, 192, 190, 0.2) !important; color: #fff !important; }
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
        <a href="dashboard.php" class="sidebar-link active" style="background: rgba(91, 192, 190, 0.15); border-right: 4px solid var(--neon-blue);">
            <i class="fa-solid fa-chalkboard-user"></i> <span class="sidebar-text">Öğretmen Paneli</span>
        </a>
        <a href="profil.php" class="sidebar-link"><i class="fa-solid fa-address-card"></i> <span class="sidebar-text">Öğretmen Profilim</span></a>
        
        <div style="position: absolute; bottom: 20px; width: 100%;">
            <a href="../../logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-power-off text-danger"></i> <span class="sidebar-text">Oturumu Kapat</span></a>
        </div>
    </nav>

    <!-- ANA İÇERİK -->
    <div class="main-content" style="padding: 30px 50px;">
        
        <!-- ÜST BAR -->
        <div class="row align-items-center mb-4 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
            <div class="col-lg-6">
                <h4 class="text-light fw-bold m-0" style="font-size: 1.4rem;">İyi çalışmalar <span class="neon-text"><?php echo htmlspecialchars($ad_soyad); ?></span></h4>
                <p class="m-0 mt-1" style="color: #A0B2C6; font-size: 0.95rem;"><?php echo htmlspecialchars($brans); ?> Alanı Eğitimcisi</p>
            </div>
            <div class="col-lg-6 d-flex justify-content-end align-items-center">
                <!-- Bildirimler -->
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
                            <li><a class="dropdown-item p-3 text-light" href="#" onclick="duyuruAc('<?php echo htmlspecialchars(addslashes($d['title'])); ?>', '<?php echo htmlspecialchars(addslashes(preg_replace('/\r|\n/', '<br>', $d['content']))); ?>')" style="white-space: normal; transition: 0.3s; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <div class="d-flex align-items-start">
                                    <i class="fa-solid fa-bullhorn text-warning mt-1 me-3 fs-5"></i>
                                    <div>
                                        <strong class="d-block text-warning" style="font-size: 0.9rem;"><?php echo htmlspecialchars($d['title']); ?></strong>
                                    </div>
                                </div>
                            </a></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><div class="p-3 text-center text-muted" style="font-size:0.85rem;">Yeni bildiriminiz yok.</div></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <button class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; border-color: rgba(255,255,255,0.2);" onclick="toggleThemeMode()" title="Gündüz/Gece Modu">
                    <i id="theme-icon-indicator" class="fa-solid fa-moon text-light"></i>
                </button>

                <a href="profil.php" class="text-decoration-none">
                    <div class="d-flex align-items-center p-1 rounded-pill shadow-sm" style="background: rgba(28, 37, 65, 0.9); border: 1px solid var(--neon-blue); padding-right: 15px !important;">
                        <div class="rounded-circle bg-dark d-flex justify-content-center align-items-center me-2 overflow-hidden" style="width: 45px; height: 45px; border: 1px solid var(--neon-blue);">
                            <?php echo $resim_html; ?>
                        </div>
                        <span class="text-light fw-bold me-2" style="font-size: 0.9rem;"><?php echo htmlspecialchars($ad_soyad); ?></span>
                    </div>
                </a>
            </div>
        </div>

        <!-- İSTATİSTİKLER (Dashboard Widget'ları) -->
        <div class="row g-4 mb-4 slide-up-fade">
            <div class="col-xl-3 col-md-6">
                <div class="teacher-stat-card border-success border-start border-4">
                    <div class="icon-box bg-success text-white"><i class="fa-solid fa-calendar-check"></i></div>
                    <p class="text-light m-0 mb-1" style="font-size: 0.95rem;">Bugünün Özeti</p>
                    <h4 class="fw-bold text-success m-0"><?php echo $bugun_gorusme_sayisi; ?> Veli Görüşmesi</h4>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="teacher-stat-card border-warning border-start border-4">
                    <div class="icon-box bg-warning text-dark"><i class="fa-solid fa-clock-rotate-left"></i></div>
                    <p class="text-light m-0 mb-1" style="font-size: 0.95rem;">Bekleyen Onaylar</p>
                    <h4 class="fw-bold text-warning m-0"><?php echo $bekleyen_onay_sayisi; ?> Yeni Talep</h4>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="teacher-stat-card border-info border-start border-4">
                    <div class="icon-box bg-info text-dark"><i class="fa-solid fa-person-chalkboard"></i></div>
                    <p class="text-light m-0 mb-1" style="font-size: 0.95rem;">Sıradaki Ders <sup>(Demo)</sup></p>
                    <h5 class="fw-bold text-info m-0 mt-2" style="font-size:1.1rem;">11/B - Web Tasarım</h5>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="teacher-stat-card border-danger border-start border-4">
                    <div class="icon-box bg-danger text-white"><i class="fa-solid fa-bullhorn"></i></div>
                    <p class="text-light m-0 mb-1" style="font-size: 0.95rem;">Son İdari Duyuru</p>
                    <div class="fw-bold text-danger m-0 mt-1 text-truncate" style="font-size:0.9rem;">
                        <?php echo $son_duyuru ? htmlspecialchars($son_duyuru['title']) : 'Yeni duyuru yok'; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 slide-up-fade" style="animation-delay: 0.1s;">
            
            <!-- SOL KOLON: BEKLEYEN TALEPLER (İşin Kalbi) -->
            <div class="col-lg-7">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="text-light fw-bold m-0"><i class="fa-solid fa-clipboard-question text-warning me-2"></i> Onay Bekleyen Randevular</h5>
                        <span class="badge bg-warning text-dark rounded-pill"><?php echo $bekleyen_onay_sayisi; ?> Bekleyen</span>
                    </div>

                    <?php if(count($bekleyen_randevular) > 0): ?>
                        <div class="d-flex flex-column gap-2" style="max-height: 500px; overflow-y: auto; padding-right: 10px;">
                            <?php foreach($bekleyen_randevular as $br): ?>
                                <div class="req-card" id="req-<?php echo $br['id']; ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="text-light fw-bold mb-1"><i class="fa-solid fa-user-tie text-muted me-1"></i> <?php echo htmlspecialchars($br['veli_ad_soyad'] ?? 'Veli'); ?></h6>
                                            <p class="m-0" style="font-size: 0.85rem; color: #A0B2C6;">
                                                <i class="fa-solid fa-children text-info"></i> Öğrenci: <b><?php echo htmlspecialchars($br['ogrenci_ad']); ?></b><br>
                                                <i class="fa-regular fa-calendar-days text-warning mt-1"></i> Talep: <b><?php echo date('d.m.Y', strtotime($br['tarih'])); ?> - <?php echo substr($br['saat'], 0, 5); ?></b>
                                            </p>
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            <button class="btn btn-sm btn-success fw-bold px-3 shadow-sm" onclick="islemYap(<?php echo $br['id']; ?>, 'onayla')"><i class="fa-solid fa-check me-1"></i> Kabul Et</button>
                                            <button class="btn btn-sm btn-outline-danger fw-bold px-3" onclick="redModalAc(<?php echo $br['id']; ?>, '<?php echo htmlspecialchars(addslashes($br['veli_ad_soyad'])); ?>', '<?php echo date('d.m.Y', strtotime($br['tarih'])); ?>', '<?php echo substr($br['saat'], 0, 5); ?>')"><i class="fa-solid fa-xmark me-1"></i> Reddet & Sebeb</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-mug-hot fs-1 text-muted mb-3 opacity-50"></i>
                            <p class="text-light mt-2" style="font-size:0.95rem;">Şu an bekleyen hiçbir randevu talebiniz yok.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SAĞ KOLON: BUGÜNKÜ ÇALIŞMA TAKVİMİ -->
            <div class="col-lg-5">
                <div class="glass-card p-4 h-100" style="border-top-color: rgba(40, 167, 69, 0.3);">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="text-light fw-bold m-0"><i class="fa-solid fa-calendar-day text-success me-2"></i> Bugünkü Görüşmeler</h5>
                        <span class="badge bg-success rounded-pill"><?php echo count($bugunku_randevular); ?> Randevu</span>
                    </div>

                    <div class="px-2" style="max-height: 500px; overflow-y: auto;">
                        <?php if(count($bugunku_randevular) > 0): ?>
                            <?php foreach($bugunku_randevular as $br): ?>
                            <div class="timeline-box" id="today-<?php echo $br['id']; ?>">
                                <div class="timeline-dot"></div>
                                <div class="req-card req-card-today p-3 m-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-dark border border-success text-success mb-2 fs-6"><i class="fa-regular fa-clock me-1"></i> <?php echo substr($br['saat'], 0, 5); ?></span>
                                            <h6 class="text-light fw-bold mb-1 m-0"><?php echo htmlspecialchars($br['veli_ad_soyad']); ?></h6>
                                            <small class="text-muted"><i class="fa-solid fa-phone fs-6"></i> <?php echo htmlspecialchars($br['telefon'] ?? '-'); ?></small>
                                        </div>
                                        <div>
                                            <button onclick="tamamlaRandevu(<?php echo $br['id']; ?>)" class="btn btn-sm btn-outline-success rounded-circle" style="width:35px; height:35px;" title="Görüşüldü - Tamamlandı Olarak İşaretle"><i class="fa-solid fa-check-double"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fa-solid fa-calendar-circle-exclamation fs-1 text-muted mb-3 opacity-50"></i>
                                <p class="text-light mt-2" style="font-size:0.95rem;">Bugün için onaylanmış bir veli görüşmeniz bulunmuyor.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- DUYURU GÖSTERİM MODALI -->
    <div class="modal fade" id="duyuruModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#111827; color:#fff; border:1px solid var(--neon-blue); box-shadow: 0 0 30px rgba(91,192,190,0.2);">
          <div class="modal-header border-0 border-bottom border-secondary">
            <h5 class="modal-title text-warning"><i class="fa-solid fa-bullhorn me-2"></i>Sistem Duyurusu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body border-0 p-4">
            <h5 id="duyuruBaslik" class="text-info fw-bold mb-3"></h5>
            <div id="duyuruIcerik" class="text-light" style="font-size: 0.95rem; line-height: 1.6;"></div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Kapat</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MAZERETLİ RED VE ALTERNATİF ÖNERME MODALI -->
    <div class="modal fade" id="redModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#111827; color:#fff; border:1px solid var(--neon-blue); box-shadow: 0 0 30px rgba(220,53,69,0.2);">
          <div class="modal-header border-0 border-bottom border-danger">
            <h5 class="modal-title text-danger"><i class="fa-solid fa-ban me-2"></i>Randevu Reddet & Öneri Sun</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body border-0 p-4">
            <p class="text-light mb-4" style="font-size:0.9rem;">
                <strong id="modalVeliAdi" class="text-warning"></strong> isimli velinin <span id="modalTarihSaat" class="badge bg-secondary"></span> tarihli randevusunu reddediyorsunuz.
            </p>
            
            <input type="hidden" id="redRandevuId">
            
            <div class="mb-3">
                <label class="form-label text-muted"><i class="fa-solid fa-comment-dots text-info"></i> Veliye İletilecek İptal Nedeni</label>
                <textarea id="redMazeret" class="form-control" rows="2" placeholder="Örn: Acil bir zümre toplantım çıktı..."></textarea>
            </div>
            
            <hr class="border-secondary my-4">
            <p class="text-success" style="font-size: 0.85rem;"><i class="fa-solid fa-lightbulb"></i> Alternatif Bir Tarih Önermek İster misiniz? (Opsiyonel)</p>
            
            <div class="row">
                <div class="col-6">
                    <label class="form-label text-muted">Alternatif Tarih</label>
                    <input type="date" id="altTarih" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-6">
                    <label class="form-label text-muted">Alternatif Saat</label>
                    <input type="time" id="altSaat" class="form-control">
                </div>
            </div>
            
          </div>
          <div class="modal-footer border-0 border-top border-secondary">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
            <button type="button" class="btn btn-danger fw-bold" onclick="reddetDevam()"><i class="fa-solid fa-paper-plane"></i> Reddet & Veliye Bildir</button>
          </div>
        </div>
      </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../assets/js/app.js?v=<?php echo time(); ?>"></script>
    <script>
        function duyuruAc(baslik, icerik) {
            document.getElementById('duyuruBaslik').innerText = baslik;
            document.getElementById('duyuruIcerik').innerHTML = icerik;
            new bootstrap.Modal(document.getElementById('duyuruModal')).show();
        }

        // Randevu Onay / Red İşlemleri (İşin Kalbi)
        async function islemYap(randevuId, islemTuru, mazeret = null, aTarih = null, aSaat = null) {
            try {
                const response = await fetch('../../api/ogretmen_randevu.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ islem: islemTuru, randevu_id: randevuId, mazeret: mazeret, alternatif_tarih: aTarih, alternatif_saat: aSaat })
                });

                const sonuc = await response.json();
                
                if (sonuc.success) {
                    Swal.fire({ icon: 'success', title: 'Başarılı!', text: sonuc.message, background: '#1C2541', color: '#fff', confirmButtonColor: '#5BC0BE' })
                    .then(() => { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Hata', text: sonuc.message, background: '#1C2541', color: '#fff' });
                }
            } catch (e) {
                console.error(e);
                Swal.fire('Hata!', 'Sunucuya bağlanırken bir sorun oluştu.', 'error');
            }
        }

        function redModalAc(rId, veliAd, tarih, saat) {
            document.getElementById('redRandevuId').value = rId;
            document.getElementById('modalVeliAdi').innerText = veliAd;
            document.getElementById('modalTarihSaat').innerText = tarih + ' ' + saat;
            
            // Temizle
            document.getElementById('redMazeret').value = '';
            document.getElementById('altTarih').value = '';
            document.getElementById('altSaat').value = '';
            
            new bootstrap.Modal(document.getElementById('redModal')).show();
        }

        function reddetDevam() {
            let id = document.getElementById('redRandevuId').value;
            let mazeret = document.getElementById('redMazeret').value;
            let trh = document.getElementById('altTarih').value;
            let sat = document.getElementById('altSaat').value;
            
            bootstrap.Modal.getInstance(document.getElementById('redModal')).hide();
            islemYap(id, 'reddet', mazeret, trh, sat);
        }

        function tamamlaRandevu(rId) {
            Swal.fire({
                title: 'Görüşme Tamamlandı mı?',
                text: "Bu görüşmeyi sistemde tamamlandı olarak işaretlemek istiyor musunuz?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Evet, Tamamlandı',
                cancelButtonText: 'Hayır',
                background: '#1C2541', color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    islemYap(rId, 'tamamla');
                }
            })
        }
    </script>
</body>
</html>
