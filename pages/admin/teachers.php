<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

if ($_SESSION['rol'] !== 'yonetici') { header("Location: ../dashboard.php"); exit; }

$ad_soyad = $_SESSION['ad_soyad'];
$mesaj = "";

// İşlemler
if (isset($_POST['ogretmen_ekle'])) {
    $adi = trim($_POST['adi']); 
    $soyadi = trim($_POST['soyadi']); 
    $email = trim($_POST['email']); 
    $brans = trim($_POST['brans']); 
    $kullanici = trim($_POST['kullanici']); // tckimlik veya kullanıcı adı
    $belirlenen_sifre = trim($_POST['sifre']); // Müdürün verdiği şifre

    if (empty($belirlenen_sifre)) {
        $belirlenen_sifre = "123456"; // Boş bırakılırsa varsayılan
    }

    $sifre_hash = password_hash($belirlenen_sifre, PASSWORD_DEFAULT);
    
    // Check if exists
    $check = $db->prepare("SELECT id FROM users WHERE tc_kimlik = ? OR kullanici_adi = ?");
    $check->execute([$kullanici, $kullanici]);
    if($check->rowCount() > 0) {
        $mesaj = "<div class='alert alert-warning'>Bu Sistem Giriş Adı veya T.C. numarası zaten başka biri tarafından kullanılıyor!</div>";
    } else {
        $stmt = $db->prepare("INSERT INTO users (adi, soyadi, email, tc_kimlik, kullanici_adi, sifre, rol, brans) VALUES (?, ?, ?, ?, ?, ?, 'ogretmen', ?)");
        $stmt->execute([$adi, $soyadi, $email, $kullanici, $kullanici, $sifre_hash, $brans]);
        $mesaj = "<div class='alert alert-success'>Yeni öğretmen sisteme başarıyla kaydedildi. (Şifre: <b>".htmlspecialchars($belirlenen_sifre)."</b>)</div>";
    }
}

// Öğretmen Şifre Sıfırlama
if (isset($_POST['sifre_sifirla_btn'])) {
    $ogr_id = (int)$_POST['ogr_id'];
    $secilen_sifre = trim($_POST['yeni_sifre']);
    
    if(!empty($secilen_sifre)) {
        $sifre_hash = password_hash($secilen_sifre, PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET sifre = ? WHERE id = ? AND rol = 'ogretmen'")->execute([$sifre_hash, $ogr_id]);
        $mesaj = "<div class='alert alert-info'>Öğretmenin şifresi başarıyla güncellendi! Yeni Şifre: <b>".htmlspecialchars($secilen_sifre)."</b></div>";
    }
}

// Öğretmen Silme
if (isset($_GET['sil'])) {
    $sil_id = (int)$_GET['sil'];
    $db->prepare("UPDATE users SET silindi_mi = 1 WHERE id = ? AND rol = 'ogretmen'")->execute([$sil_id]);
    header("Location: teachers.php"); exit;
}

$ogretmenler = $db->query("SELECT * FROM users WHERE rol='ogretmen' AND silindi_mi=0 ORDER BY adi ASC")->fetchAll();
$branslar = $db->query("SELECT * FROM branches ORDER BY name ASC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Eğitim Kadrosu Yönetimi | Ahi Evran MTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .custom-teacher-input {
            width: 100% !important;
            background: rgba(0,0,0,0.3) !important;
            border: 1px solid rgba(91,192,190,0.3) !important;
            color: var(--text-light) !important;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: none !important;
            transition: all 0.3s ease;
        }
        .custom-teacher-input:focus {
            border-color: var(--neon-blue) !important;
            background: rgba(0,0,0,0.5) !important;
            box-shadow: 0 0 10px rgba(91,192,190,0.3) !important;
        }
        .custom-teacher-input::placeholder { color: rgba(160, 178, 198, 0.4); }
        .teacher-label { color: #A0B2C6; font-size: 0.85rem; font-weight: 500; margin-bottom: 5px; display: block; }
        
        .table-custom-dark {
            --bs-table-bg: transparent;
            background: transparent;
        }
        .table-custom-dark th { border-bottom: 1px solid var(--neon-blue); color: var(--neon-blue); font-weight: normal; background: transparent !important; }
        .table-custom-dark td { border-bottom: 1px solid rgba(255,255,255,0.05); color: #E6F1F9; vertical-align: middle; background: transparent !important; }
        .table-custom-dark tr:hover td { background: rgba(91,192,190,0.05) !important; }
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
        <a href="teachers.php" class="sidebar-link active" style="background: rgba(91, 192, 190, 0.15); border-right: 4px solid var(--neon-blue);"><i class="fa-solid fa-chalkboard-user"></i> <span class="sidebar-text">Eğitim Kadrosu</span></a>
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
        <h4 class="text-light fw-bold mb-4"><i class="fa-solid fa-chalkboard-user text-info me-2"></i>Eğitim Kadrosu Yönetimi</h4>
        <?php echo $mesaj; ?>

        <div class="row g-4 mb-4">
            <!-- Öğretmen Ekleme Formu -->
            <div class="col-xl-4 col-lg-5">
                <div class="glass-card p-4 border border-info">
                    <h5 class="text-light mb-4"><i class="fa-solid fa-user-plus text-info me-2"></i>Yeni Sisteme Ekle</h5>
                    <form action="teachers.php" method="POST">
                        <div class="mb-3">
                            <label class="teacher-label">Öğretmen Adı</label>
                            <input type="text" name="adi" class="custom-teacher-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="teacher-label">Öğretmen Soyadı</label>
                            <input type="text" name="soyadi" class="custom-teacher-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="teacher-label">Sisteme Giriş T.C. / Kullanıcı Adı</label>
                            <input type="text" name="kullanici" class="custom-teacher-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="teacher-label">Sistem Şifresi <small class="text-light">(Boş: 123456)</small></label>
                            <input type="text" name="sifre" class="custom-teacher-input" placeholder="Yazmazsanız 123456 olur">
                        </div>
                        <div class="mb-3">
                            <label class="teacher-label">Kurumsal E-Posta</label>
                            <input type="email" name="email" class="custom-teacher-input" placeholder="ad@ahievran.edu.tr">
                        </div>
                        <div class="mb-4">
                            <label class="teacher-label">Meslek Alanı / Branş</label>
                            <select name="brans" class="custom-teacher-input" required>
                                <option value="" style="background:#1C2541;">Branş Seçiniz</option>
                                <?php foreach($branslar as $b): ?>
                                    <option value="<?php echo htmlspecialchars($b['name']); ?>" style="background:#1C2541;"><?php echo htmlspecialchars($b['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="ogretmen_ekle" class="btn btn-info w-100 fw-bold border border-info shadow-sm text-dark"><i class="fa-solid fa-user-check"></i> Kaydet ve Yetkilendir</button>
                    </form>
                </div>
            </div>

            <!-- Öğretmen Listesi -->
            <div class="col-xl-8 col-lg-7">
                <div class="glass-card p-4 h-100">
                    <h5 class="text-light mb-4">Mevcut Eğitim Kadrosu (<?php echo count($ogretmenler); ?>)</h5>
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-custom-dark align-middle">
                            <thead class="sticky-top bg-dark">
                                <tr>
                                    <th>Eğitmen Profili</th>
                                    <th>Branş/Alan</th>
                                    <th>Sisteme Giriş Adı</th>
                                    <th class="text-end">Aksiyonlar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ogretmenler as $o): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../../assets/img/<?php echo $o['profil_resmi']; ?>" class="rounded-circle me-3 border border-secondary" width="40" height="40" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($o['adi'].'+'.$o['soyadi']); ?>&background=1C2541&color=5BC0BE'">
                                            <div>
                                                <span class="fw-bold d-block"><?php echo htmlspecialchars($o['adi'] . ' ' . $o['soyadi']); ?></span>
                                                <small class="text-muted"><?php echo htmlspecialchars($o['email'] ?? 'E-posta belirtilmedi'); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge" style="background: rgba(91,192,190,0.2); color: var(--neon-blue); border:1px solid var(--neon-blue);"><?php echo htmlspecialchars($o['brans']); ?></span></td>
                                    <td class="text-info font-monospace"><?php echo htmlspecialchars($o['kullanici_adi'] ? $o['kullanici_adi'] : $o['tc_kimlik']); ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-warning me-1" onclick="sifreSifirlaModal(<?php echo $o['id']; ?>, '<?php echo htmlspecialchars(addslashes($o['adi'].' '.$o['soyadi'])); ?>')" title="Şifre Değiştir"><i class="fa-solid fa-key"></i></button>
                                        <a href="?sil=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Silmek istediğinize emin misiniz? Randevuları olan bir hesapsa sistem uyarısı verebilir.');" title="Sistemden Sil"><i class="fa-solid fa-trash"></i></a>
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
    
    <!-- Şifre Sıfırlama Modalı -->
    <div class="modal fade" id="sifreModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#111827; color:#fff; border:1px solid var(--neon-blue); box-shadow: 0 0 30px rgba(91,192,190,0.2);">
          <div class="modal-header border-0 border-bottom border-secondary">
            <h5 class="modal-title text-warning"><i class="fa-solid fa-key me-2"></i>Şifre Yetkilendirmesi</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <form action="teachers.php" method="POST">
              <div class="modal-body border-0 p-4">
                <div class="mb-2">
                    <span class="text-muted" style="font-size:0.9rem;">İşlem Yapılan Eğitici:</span>
                    <h6 id="modalOgretmenAd" class="text-info fw-bold">Yükleniyor...</h6>
                </div>
                <input type="hidden" name="ogr_id" id="modalOgrId">
                <div class="mt-4">
                    <label class="teacher-label">Yeni Sisteme Giriş Şifresi</label>
                    <input type="text" name="yeni_sifre" class="custom-teacher-input" required>
                </div>
              </div>
              <div class="modal-footer border-0 border-top border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal, Kapat</button>
                <button type="submit" name="sifre_sifirla_btn" class="btn btn-warning"><i class="fa-solid fa-check"></i> Yeni Şifreyi Belirle</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="../../assets/js/app.js?v=<?php echo time(); ?>"></script>
    <script>
        function sifreSifirlaModal(id, ad) {
            document.getElementById('modalOgrId').value = id;
            document.getElementById('modalOgretmenAd').innerText = ad;
            new bootstrap.Modal(document.getElementById('sifreModal')).show();
        }
    </script>
</body>
</html>
