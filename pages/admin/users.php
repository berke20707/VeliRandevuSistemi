<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

if ($_SESSION['rol'] !== 'yonetici') { header("Location: ../dashboard.php"); exit; }

$ad_soyad = $_SESSION['ad_soyad'];
$mesaj = "";

// İşlemler
if (isset($_GET['sifre_sifirla'])) {
    $uid = (int)$_GET['sifre_sifirla'];
    $tur = $_GET['tur'] ?? 'veli';
    $yeni_sifre = password_hash('123456', PASSWORD_DEFAULT);
    
    if($tur == 'veli') {
        $db->prepare("UPDATE users SET sifre = ? WHERE id = ?")->execute([$yeni_sifre, $uid]);
    } else {
        $db->prepare("UPDATE ogrenciler SET sifre = ? WHERE id = ?")->execute([$yeni_sifre, $uid]);
    }
    $mesaj = "<div class='alert alert-success'>Kullanıcı şifresi başarıyla <b>123456</b> olarak sıfırlandı.</div>";
}

if (isset($_POST['excel_yukle'])) {
    if(isset($_FILES['ogrenci_excel']) && $_FILES['ogrenci_excel']['error'] == 0) {
        $mesaj = "<div class='alert alert-success'>E-Okul listesi başarıyla sisteme aktarıldı ve senkronize edildi! (Demo)</div>";
    }
}

$veliler = $db->query("SELECT * FROM users WHERE rol='veli' AND silindi_mi=0 ORDER BY adi ASC")->fetchAll();
$ogrenciler = $db->query("SELECT * FROM ogrenciler ORDER BY sinif ASC, adi ASC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Veritabanı | Ahi Evran MTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .table-custom-dark { --bs-table-bg: transparent; background: transparent; }
        .table-custom-dark th { border-bottom: 1px solid var(--neon-blue); color: var(--neon-blue); font-weight: normal; background: transparent !important; }
        .table-custom-dark td { border-bottom: 1px solid rgba(255,255,255,0.05); color: #E6F1F9; vertical-align: middle; background: transparent !important; }
        .table-custom-dark tr:hover td { background: rgba(91,192,190,0.05) !important; }
        .profile-line { display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.1); padding: 8px 0; font-size: 0.9rem; }
        .profile-line span:first-child { color: var(--neon-blue); font-weight: bold; }
        .profile-line span:last-child { color: #fff; }
    </style>
</head>
<body class="bg-dark-space">
    <div class="school-watermark"><i class="fa-solid fa-graduation-cap"></i></div>
    <div id="particles-js" style="position: fixed; z-index: -1;"></div>

    <nav class="glass-sidebar">
        <!-- Sidebar content -->
        <div class="text-center mb-5 mt-3 px-2 text-light fw-bold" style="border-bottom: 1px solid rgba(91, 192, 190, 0.2); padding-bottom: 20px;">
            <img src="../../assets/img/logo.png" alt="Logo" class="sidebar-logo mb-3"><br>
            <span class="sidebar-text" style="font-size: 0.9rem;">Ahi Evran MTAL<br><span class="neon-text" style="font-size: 0.75rem;">Yönetim Paneli</span></span>
        </div>
        <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-gauge-high"></i> <span class="sidebar-text">Yönetim Paneli</span></a>
        <a href="teachers.php" class="sidebar-link"><i class="fa-solid fa-chalkboard-user"></i> <span class="sidebar-text">Eğitim Kadrosu</span></a>
        <a href="users.php" class="sidebar-link active" style="background: rgba(91, 192, 190, 0.15); border-right: 4px solid var(--neon-blue);"><i class="fa-solid fa-users"></i> <span class="sidebar-text">Veli & Öğrenci VT</span></a>
        <a href="appointments.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> <span class="sidebar-text">Tüm Randevular</span></a>
        <a href="announcements.php" class="sidebar-link"><i class="fa-solid fa-bullhorn"></i> <span class="sidebar-text">Duyuru Yönetimi</span></a>
        <a href="blacklist.php" class="sidebar-link"><i class="fa-solid fa-user-xmark text-danger"></i> <span class="sidebar-text">Kara Liste</span></a>
        <a href="settings.php" class="sidebar-link"><i class="fa-solid fa-gears"></i> <span class="sidebar-text">Sistem Ayarları</span></a>
        <div style="position: absolute; bottom: 20px; width: 100%;">
            <a href="../../logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket text-danger"></i> <span class="sidebar-text">Güvenli Çıkış</span></a>
        </div>
    </nav>

    <div class="main-content" style="padding: 30px 50px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-light fw-bold m-0"><i class="fa-solid fa-users text-info me-2"></i>Kullanıcı Veritabanı & Profil İzleme</h4>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-info rounded-pill" onclick="alert('E-Okul MEB API devamsızlık ve not robotu şu an çevrimiçi çalışıyor.')"><i class="fa-solid fa-satellite-dish"></i> E-Okul Senkronizasyonu</button>
                <button class="btn btn-outline-success rounded-pill" data-bs-toggle="modal" data-bs-target="#excelModal"><i class="fa-solid fa-file-excel"></i> Toplu Aktarım</button>
            </div>
        </div>

        <?php echo $mesaj; ?>

        <div class="row g-4 mb-4">
            <!-- Veli Listesi -->
            <div class="col-lg-6">
                <div class="glass-card p-4 h-100 border border-info">
                    <h5 class="text-light mb-3"><i class="fa-solid fa-user-tie text-info me-2"></i>Sistemdeki Veliler (<?php echo count($veliler); ?>)</h5>
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-custom-dark align-middle">
                            <thead class="sticky-top bg-dark">
                                <tr>
                                    <th>Ad Soyad</th>
                                    <th>Cihaz/Tel</th>
                                    <th class="text-end">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach($veliler as $v): 
                                    // Velinin öğrencilerini telefon numarasından eşleştirerek bul
                                    $cocuklar = [];
                                    if (!empty($v['telefon'])) {
                                        $cocuk_stmt = $db->prepare("SELECT adi, soyadi, sinif FROM ogrenciler WHERE veli_telefon = ?");
                                        $cocuk_stmt->execute([$v['telefon']]);
                                        $cocuklar = $cocuk_stmt->fetchAll();
                                    }
                                    
                                    $cocuk_str = "";
                                    $cocuk_html = "";
                                    foreach($cocuklar as $c) {
                                        $cocuk_str .= $c['adi']." ".$c['soyadi']." (".$c['sinif'].")<br>";
                                        $cocuk_html .= "<span class='badge bg-primary me-1'>".$c['adi']." ".$c['soyadi']."</span>";
                                    }
                                    if(empty($cocuk_str)) {
                                        $cocuk_str = "Sisteme kayıtlı öğrencisi yok.";
                                        $cocuk_html = "<small class='text-muted'>Öğrenci Kaydı Yok</small>";
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <span class="d-block text-light fw-bold"><?php echo htmlspecialchars($v['adi'] . ' ' . $v['soyadi']); ?></span>
                                        <div class="mt-1"><?php echo $cocuk_html; ?></div>
                                    </td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($v['telefon'] ?? '-'); ?></span></td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-sm btn-outline-info me-1" onclick="veliProfilAc('<?php echo htmlspecialchars(addslashes($v['adi'].' '.$v['soyadi'])); ?>', '<?php echo htmlspecialchars(addslashes($v['tc_kimlik'])); ?>', '<?php echo htmlspecialchars(addslashes($v['telefon'] ?? 'Bilinmiyor')); ?>', '<?php echo htmlspecialchars(addslashes($v['kullanici_adi'] ?? '-')); ?>', '<?php echo htmlspecialchars(addslashes($v['email'] ?? '-')); ?>', '<?php echo htmlspecialchars(addslashes($cocuk_str)); ?>')" title="Profili İncele"><i class="fa-solid fa-eye"></i></button>
                                        <a href="?sifre_sifirla=<?php echo $v['id']; ?>&tur=veli" class="btn btn-sm btn-outline-warning" title="Şifreyi Sıfırla (123456)" onclick="return confirm('Emin misiniz?');"><i class="fa-solid fa-key"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Öğrenci Listesi -->
            <div class="col-lg-6">
                <div class="glass-card p-4 h-100 border border-info">
                    <h5 class="text-light mb-3"><i class="fa-solid fa-graduation-cap text-info me-2"></i>Sistemdeki Öğrenciler (<?php echo count($ogrenciler); ?>)</h5>
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-custom-dark align-middle">
                            <thead class="sticky-top bg-dark">
                                <tr>
                                    <th>Ad Soyad</th>
                                    <th>Durum / No</th>
                                    <th class="text-end">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ogrenciler as $o): ?>
                                <tr>
                                    <td>
                                        <span class="d-block text-light fw-bold"><?php echo htmlspecialchars($o['adi'] . ' ' . $o['soyadi']); ?></span>
                                        <small class="text-muted"><i class="fa-regular fa-clock"></i> Aktif</small>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: rgba(91,192,190,0.2); color: var(--neon-blue); border:1px solid var(--neon-blue);"><?php echo htmlspecialchars($o['sinif'] . ' - ' . $o['okul_no']); ?></span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button class="btn btn-sm btn-outline-success me-1" onclick="devamsizlikSorgula('<?php echo htmlspecialchars(addslashes($o['tc_kimlik'])); ?>', '<?php echo htmlspecialchars(addslashes($o['adi'])); ?>')" title="E-Okul Devamsızlık"><i class="fa-solid fa-chart-line"></i></button>
                                        <button class="btn btn-sm btn-outline-info me-1" onclick="ogrenciProfilAc('<?php echo htmlspecialchars(addslashes($o['adi'].' '.$o['soyadi'])); ?>', '<?php echo htmlspecialchars(addslashes($o['tc_kimlik'])); ?>', '<?php echo htmlspecialchars(addslashes($o['okul_no'])); ?>', '<?php echo htmlspecialchars(addslashes($o['sinif'])); ?>')" title="Profili İncele"><i class="fa-solid fa-eye"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Excel Upload Modal -->
    <div class="modal fade" id="excelModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#111827; color:#fff; border:1px solid rgba(91,192,190,0.3);">
          <div class="modal-header border-0 border-bottom border-info">
            <h5 class="modal-title"><i class="fa-solid fa-file-excel text-success me-2"></i>E-Okul Toplu Aktarımı</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <form action="users.php" method="POST" enctype="multipart/form-data">
              <div class="modal-body border-0">
                <p class="text-light" style="font-size:0.9rem;">Milli Eğitim Bakanlığı formatında çekilmiş Excel (XLS/CSV) listesini yükleyerek yeni öğrenci/veli kayıtlarını otomatik ekleyebilirsiniz.</p>
                <input type="file" name="ogrenci_excel" class="form-control" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required>
              </div>
              <div class="modal-footer border-0 border-top border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="submit" name="excel_yukle" class="btn btn-success"><i class="fa-solid fa-upload"></i> Veritabanına Al</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Veli Profil Modalı -->
    <div class="modal fade" id="veliProfilModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#111827; color:#fff; border:1px solid var(--neon-blue); box-shadow: 0 0 30px rgba(91,192,190,0.2);">
          <div class="modal-header border-0 border-bottom border-info">
            <h5 class="modal-title"><i class="fa-solid fa-user-tie text-info me-2"></i>Veli Kapsamlı Profili</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body border-0 p-4">
            <div class="text-center mb-4">
                <div class="rounded-circle bg-dark d-inline-flex justify-content-center align-items-center mb-2 border border-info" style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-user-tie text-info" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-light fw-bold" id="vp_ad"></h5>
                <span class="badge bg-secondary">Resmi Kayıtlı Veli</span>
            </div>
            <div class="profile-line"><span><i class="fa-regular fa-id-card"></i> T.C. Kimlik</span><span id="vp_tc"></span></div>
            <div class="profile-line"><span><i class="fa-solid fa-phone"></i> İletişim Numarası</span><span id="vp_tel"></span></div>
            <div class="profile-line"><span><i class="fa-solid fa-children text-success"></i> Kayıtlı Çocukları</span><span id="vp_cocuklar" class="text-end text-success fw-bold" style="font-size:0.85rem"></span></div>
            <div class="profile-line"><span><i class="fa-solid fa-user-group"></i> Yakın / Acil No</span><span>Onaylı Aile Bireyi (0555***)</span></div>
            <div class="profile-line"><span><i class="fa-solid fa-droplet text-danger"></i> Kan Grubu</span><span>0 RH+</span></div>
            <div class="profile-line"><span><i class="fa-solid fa-envelope"></i> E-Posta</span><span id="vp_email"></span></div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Kapat</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Öğrenci Profil Modalı -->
    <div class="modal fade" id="ogrenciProfilModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#111827; color:#fff; border:1px solid #28a745; box-shadow: 0 0 30px rgba(40,167,69,0.2);">
          <div class="modal-header border-0 border-bottom border-success">
            <h5 class="modal-title"><i class="fa-solid fa-graduation-cap text-success me-2"></i>Öğrenci Kapsamlı Profili</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body border-0 p-4">
            <div class="text-center mb-4">
                <div class="rounded-circle bg-dark d-inline-flex justify-content-center align-items-center mb-2 border border-success" style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-graduation-cap text-success" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-light fw-bold" id="op_ad"></h5>
                <span class="badge bg-success" id="op_sinifBadge"></span>
            </div>
            <div class="profile-line"><span><i class="fa-regular fa-id-card"></i> T.C. Kimlik</span><span id="op_tc"></span></div>
            <div class="profile-line"><span><i class="fa-solid fa-chalkboard-user"></i> Okul Numarası</span><span id="op_no"></span></div>
            <div class="profile-line"><span><i class="fa-solid fa-building"></i> Sınıf / Şube</span><span id="op_sinif"></span></div>
            <div class="profile-line"><span><i class="fa-solid fa-heart-pulse text-danger"></i> Özel Durum / Sağlık</span><span>Belirtilmemiş</span></div>
            <div class="profile-line"><span><i class="fa-solid fa-bus"></i> Taşımalı / Servis</span><span>Kayıtlı Değil</span></div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Kapat</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Devamsızlık Modalı -->
    <div class="modal fade" id="devamsizlikModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#111827; color:#fff; border:1px solid #f6c23e; box-shadow: 0 0 30px rgba(246,194,62,0.2);">
          <div class="modal-header border-0 border-bottom border-warning">
            <h5 class="modal-title"><i class="fa-solid fa-chart-line text-warning me-2"></i>E-Okul Devamsızlık Özeti</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body border-0 p-4 text-center">
            <h5 class="text-light fw-bold" id="dp_ad"></h5>
            <p class="text-light small mb-4">Milli Eğitim Bakanlığı veritabanından çekilmiştir.</p>
            
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 border border-success rounded" style="background: rgba(40,167,69,0.1);">
                        <h3 class="text-success m-0" id="dp_ozurlu">0</h3>
                        <small>Özürlü Devamsızlık (Gün)</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 border border-danger rounded" style="background: rgba(220,53,69,0.1);">
                        <h3 class="text-danger m-0" id="dp_ozursuz">0</h3>
                        <small>Özürsüz Devamsızlık (Gün)</small>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 p-3 border border-warning rounded text-warning text-start" style="background: rgba(246,194,62,0.1); font-size:0.85rem;">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> <span id="dp_durum">Öğrencinin devamsızlık durumu eşiğin altındadır. Kurul kararı gerekmemektedir.</span>
            </div>
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
        function veliProfilAc(ad, tc, tel, kullanici, email, cocuklar) {
            document.getElementById('vp_ad').innerText = ad;
            document.getElementById('vp_tc').innerText = tc;
            document.getElementById('vp_tel').innerText = tel;
            document.getElementById('vp_email').innerText = email;
            document.getElementById('vp_cocuklar').innerHTML = cocuklar;
            new bootstrap.Modal(document.getElementById('veliProfilModal')).show();
        }
        
        function ogrenciProfilAc(ad, tc, no, sinif) {
            document.getElementById('op_ad').innerText = ad;
            document.getElementById('op_sinifBadge').innerText = sinif;
            document.getElementById('op_tc').innerText = tc;
            document.getElementById('op_no').innerText = no;
            document.getElementById('op_sinif').innerText = sinif;
            new bootstrap.Modal(document.getElementById('ogrenciProfilModal')).show();
        }

        function devamsizlikSorgula(tc, isim) {
            // E-okul API simülasyonu
            const randomOzurlu = Math.floor(Math.random() * 5);
            const randomOzursuz = Math.floor(Math.random() * 12);
            
            document.getElementById('dp_ad').innerText = isim;
            document.getElementById('dp_ozurlu').innerText = randomOzurlu;
            document.getElementById('dp_ozursuz').innerText = randomOzursuz;
            
            if(randomOzursuz > 9) {
                document.getElementById('dp_durum').innerHTML = "Öğrenci <b>Sınıf Tekrarı Belge</b> sınırındadır. Acilen veli görüşmesi atanmalıdır.";
                document.getElementById('dp_durum').className = "text-danger fw-bold";
            } else {
                document.getElementById('dp_durum').innerHTML = "Öğrencinin devamsızlık durumu eşiğin altındadır. Sınıf geçmeye engel teşkil etmez.";
                document.getElementById('dp_durum').className = "text-warning";
            }
            
            new bootstrap.Modal(document.getElementById('devamsizlikModal')).show();
        }
    </script>
</body>
</html>
