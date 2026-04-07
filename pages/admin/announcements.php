<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

if ($_SESSION['rol'] !== 'yonetici') { header("Location: ../dashboard.php"); exit; }

$ad_soyad = $_SESSION['ad_soyad'];
$mesaj = "";

if (isset($_POST['duyuru_yayinla'])) {
    $baslik = trim($_POST['title']);
    $icerik = trim($_POST['content']);
    $hedef = $_POST['hedef']; // hepsi, veli, ogrenci, ogretmen

    $db->prepare("INSERT INTO announcements (title, content, hedef, is_active) VALUES (?, ?, ?, 1)")->execute([$baslik, $icerik, $hedef]);
    
    // Herkese bildirim at
    $mesaj = "<div class='alert alert-success'>Duyuru başarıyla yayınlandı. İlgili kullanıcıların bildirim menüsünde anlık olarak görünecektir.</div>";
}

if (isset($_GET['sil'])) {
    $db->prepare("DELETE FROM announcements WHERE id = ?")->execute([(int)$_GET['sil']]);
    header("Location: announcements.php"); exit;
}

$duyurular = $db->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Duyuru Merkezi | Ahi Evran MTAL</title>
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
        <a href="announcements.php" class="sidebar-link active" style="background: rgba(91, 192, 190, 0.15); border-right: 4px solid var(--neon-blue);"><i class="fa-solid fa-bullhorn"></i> <span class="sidebar-text">Duyuru Yönetimi</span></a>
        <a href="blacklist.php" class="sidebar-link"><i class="fa-solid fa-user-xmark text-danger"></i> <span class="sidebar-text">Kara Liste</span></a>
        <a href="settings.php" class="sidebar-link"><i class="fa-solid fa-gears"></i> <span class="sidebar-text">Sistem Ayarları</span></a>
        <div style="position: absolute; bottom: 20px; width: 100%;">
            <a href="../../logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket text-danger"></i> <span class="sidebar-text">Güvenli Çıkış</span></a>
        </div>
    </nav>

    <div class="main-content" style="padding: 30px 50px;">
        <h4 class="text-light fw-bold mb-4"><i class="fa-solid fa-bullhorn text-warning me-2"></i>Aktif Duyuru & İletişim Merkezi</h4>
        <?php echo $mesaj; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="glass-card p-4 h-100">
                    <h5 class="text-light mb-4">Yeni Duyuru Oluştur</h5>
                    <form action="announcements.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-info">Başlık</label>
                            <input type="text" name="title" class="form-control" placeholder="Örn: 1. Dönem Veli Toplantısı" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-info">Hedef Kitle</label>
                            <select name="hedef" class="form-select">
                                <option value="hepsi">Tüm Sistem (Herkes)</option>
                                <option value="ogretmen">Sadece Öğretmenler</option>
                                <option value="veli">Sadece Veliler</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-info">Açıklama / İçerik</label>
                            <textarea name="content" class="form-control" rows="5" placeholder="Duyuru metni buraya girilecek..." required></textarea>
                        </div>
                        <button type="submit" name="duyuru_yayinla" class="btn btn-warning w-100 fw-bold border border-warning shadow-sm"><i class="fa-solid fa-paper-plane"></i> Gönder ve Yayınla</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="glass-card p-4 h-100">
                    <h5 class="text-light mb-3">Sistemdeki Duyurular</h5>
                    <div class="d-flex flex-column gap-3" style="max-height: 500px; overflow-y: auto;">
                        <?php foreach($duyurular as $d): ?>
                        <div class="p-3 border rounded shadow-sm position-relative" style="background: rgba(28,37,65,0.7); border-color: rgba(91,192,190,0.3) !important;">
                            <a href="?sil=<?php echo $d['id']; ?>" class="position-absolute end-0 top-0 m-2 text-danger" title="Duyuruyu Sil" onclick="return confirm('Bu duyuru kalıcı olarak silinecektir.');"><i class="fa-solid fa-trash"></i></a>
                            <h6 class="text-warning fw-bold mb-1"><?php echo htmlspecialchars($d['title']); ?></h6>
                            <p class="text-light m-0" style="font-size: 0.9rem;"><?php echo nl2br(htmlspecialchars($d['content'])); ?></p>
                            <small class="text-muted d-block mt-2"><i class="fa-regular fa-clock"></i> <?php echo date('d.m.Y H:i', strtotime($d['created_at'])); ?></small>
                        </div>
                        <?php endforeach; ?>

                        <?php if(count($duyurular) == 0): ?>
                            <div class="text-center py-5">
                                <i class="fa-solid fa-inbox fs-2 text-muted mb-2"></i>
                                <p class="text-light">Hiç duyuru yok.</p>
                            </div>
                        <?php endif; ?>
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

