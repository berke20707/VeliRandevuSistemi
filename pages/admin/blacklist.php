<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

if ($_SESSION['rol'] !== 'yonetici') { header("Location: ../dashboard.php"); exit; }

$ad_soyad = $_SESSION['ad_soyad'];
$mesaj = "";

// Kara Listeye Ekle
if (isset($_POST['blacklist_ekle'])) {
    $hedef = trim($_POST['hedef']);
    $sebep = trim($_POST['sebep']);
    
    // Kimin olduğunu telefon veya TC'den tespit et
    $stmt = $db->prepare("SELECT adi, soyadi FROM users WHERE tc_kimlik = ? OR telefon = ? OR kullanici_adi = ? LIMIT 1");
    $stmt->execute([$hedef, $hedef, $hedef]);
    $kullanici = $stmt->fetch();
    
    if ($kullanici) {
        $isim = $kullanici['adi'] . ' ' . $kullanici['soyadi'];
    } else {
        $isim = "Sisteme Kayıtsız Dış Kullanıcı";
    }
    
    $db->prepare("INSERT INTO blacklist (tc_kimlik, ad_soyad, sebep) VALUES (?, ?, ?)")->execute([$hedef, $isim, $sebep]);
    $mesaj = "<div class='alert alert-danger'>Vatandaş kara listeye başarıyla alındı. Tespit edilen isim: <b>".$isim."</b>. Artık sistem üzerinden randevu alamayacak.</div>";
}

// Kara Listeden Çıkar
if (isset($_GET['cikar'])) {
    $db->prepare("DELETE FROM blacklist WHERE id = ?")->execute([(int)$_GET['cikar']]);
    header("Location: blacklist.php"); exit;
}

$blacklist = $db->query("SELECT * FROM blacklist ORDER BY created_at DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kara Liste | Ahi Evran MTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body class="bg-dark-space">
    <div class="school-watermark"><i class="fa-solid fa-graduation-cap"></i></div>
    <div id="particles-js" style="position: fixed; z-index: -1;"></div>

    <nav class="glass-sidebar">
        <div class="text-center mb-5 mt-3 px-2 text-light fw-bold" style="border-bottom: 1px solid rgba(91, 192, 190, 0.2); padding-bottom: 20px;">
            <img src="../../assets/img/logo.png" alt="Logo" class="sidebar-logo mb-3"><br>
            <span class="sidebar-text" style="font-size: 0.9rem;">Ahi Evran MTAL<br><span class="neon-text" style="font-size: 0.75rem;">Yönetim Paneli</span></span>
        </div>
        <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-gauge-high"></i> <span class="sidebar-text">Yönetim Paneli</span></a>
        <a href="teachers.php" class="sidebar-link"><i class="fa-solid fa-chalkboard-user"></i> <span class="sidebar-text">Eğitim Kadrosu</span></a>
        <a href="users.php" class="sidebar-link"><i class="fa-solid fa-users"></i> <span class="sidebar-text">Veli & Öğrenci VT</span></a>
        <a href="appointments.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> <span class="sidebar-text">Tüm Randevular</span></a>
        <a href="announcements.php" class="sidebar-link"><i class="fa-solid fa-bullhorn"></i> <span class="sidebar-text">Duyuru Yönetimi</span></a>
        <a href="blacklist.php" class="sidebar-link active" style="background: rgba(220, 53, 69, 0.15); border-right: 4px solid #dc3545;"><i class="fa-solid fa-user-xmark text-danger"></i> <span class="sidebar-text fw-bold text-danger">Kara Liste</span></a>
        <a href="settings.php" class="sidebar-link"><i class="fa-solid fa-gears"></i> <span class="sidebar-text">Sistem Ayarları</span></a>
        <div style="position: absolute; bottom: 20px; width: 100%;">
            <a href="../../logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket text-danger"></i> <span class="sidebar-text">Güvenli Çıkış</span></a>
        </div>
    </nav>

    <div class="main-content" style="padding: 30px 50px;">
        <h4 class="text-danger fw-bold mb-4"><i class="fa-solid fa-shield-halved me-2"></i>Kara Liste (Blacklist) Denetimi</h4>
        <?php echo $mesaj; ?>
        
        <div class="alert alert-dark border border-danger text-light mb-4 shadow-sm" style="background: rgba(0,0,0,0.5);">
            <i class="fa-solid fa-triangle-exclamation text-danger fs-5 me-2"></i><b>Uyarı:</b> Buraya eklenen vatandaşların mevcut hesapları askıya alınır ve T.C. kimlik numaralarıyla artık sisteme giriş yapamaz veya yeni hesap açıp randevu talep edemezler.
        </div>

        <div class="row g-4 mb-4">
            <!-- Ekleme Formu -->
            <div class="col-lg-4">
                <div class="glass-card p-4 border border-danger">
                    <h5 class="text-light mb-4">Kara Listeye Ekle</h5>
                    <form action="blacklist.php" method="POST">
                        <div class="mb-3">
                            <div class="alert alert-info p-2 mb-3" style="font-size: 0.8rem; background: rgba(13,202,240,0.1); border: 1px solid #0dcaf0; color: #0dcaf0;">
                                <i class="fa-solid fa-circle-info"></i> Sadece kullanıcının Telefon veya T.C. numarasını yazarak engelleyin. Sistem adını ve soyadını otomatik bulacaktır.
                            </div>
                            <label class="form-label text-light">T.C. Kimlik veya Telefon Numarası</label>
                            <input type="text" name="hedef" class="form-control" maxlength="11" placeholder="Örn: 0555... veya 123..." required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-light">Engelleme Nedeni</label>
                            <textarea name="sebep" class="form-control" rows="3" placeholder="Örn: 3 kere randevuya gelmedi." required></textarea>
                        </div>
                        <button type="submit" name="blacklist_ekle" class="btn btn-danger w-100 fw-bold shadow"><i class="fa-solid fa-ban"></i> Listeye Ekle ve Engelle</button>
                    </form>
                </div>
            </div>

            <!-- Liste -->
            <div class="col-lg-8">
                <div class="glass-card p-4 h-100">
                    <h5 class="text-light mb-3">Kara Listedeki Vatandaşlar (<?php echo count($blacklist); ?>)</h5>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle">
                            <thead class="bg-dark">
                                <tr>
                                    <th>Kişi</th>
                                    <th>T.C. Kimlik</th>
                                    <th>Neden?</th>
                                    <th class="text-end">Aksiyon</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($blacklist as $b): ?>
                                <tr>
                                    <td class="text-warning fw-bold"><i class="fa-solid fa-user-injured me-2"></i> <?php echo htmlspecialchars($b['ad_soyad']); ?></td>
                                    <td><?php echo htmlspecialchars($b['tc_kimlik']); ?></td>
                                    <td class="text-light" style="font-size: 0.85rem; max-width: 250px;"><?php echo htmlspecialchars($b['sebep']); ?></td>
                                    <td class="text-end">
                                        <a href="?cikar=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Bu vatandaşı affedip listeden çıkarıyorsunuz. Emin misiniz?');"><i class="fa-solid fa-user-check"></i> Listeden Çıkar</a>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="../../assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>

