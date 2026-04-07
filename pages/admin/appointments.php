<?php
require_once '../../includes/auth_check.php';
require_once '../../config/database.php';

if ($_SESSION['rol'] !== 'yonetici') { header("Location: ../dashboard.php"); exit; }

$ad_soyad = $_SESSION['ad_soyad'];
$mesaj = "";

// Randevu iptal işlemi (İdari İzin vs)
if (isset($_GET['iptal_et'])) {
    $r_id = (int)$_GET['iptal_et'];
    $db->prepare("DELETE FROM randevular WHERE id = ?")->execute([$r_id]);
    $mesaj = "<div class='alert alert-danger'>Randevu idari işlem nedeniyle kalıcı olarak sistemden silindi.</div>";
}

// Tüm randevuları çek (gelecekten geçmişe sıralı)
$randevular = $db->query("
    SELECT r.*, 
        u1.adi as veli_ad, u1.soyadi as veli_soyad, 
        u2.adi as ogr_ad, u2.soyadi as ogr_soyad, u2.brans 
    FROM randevular r 
    LEFT JOIN users u1 ON r.veli_id = u1.id 
    LEFT JOIN users u2 ON CONCAT(u2.adi, ' ', u2.soyadi) COLLATE utf8mb4_unicode_ci = r.ogretmen_ad COLLATE utf8mb4_unicode_ci 
    ORDER BY r.tarih DESC, r.saat ASC
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Randevu Denetimi | Ahi Evran MTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
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
        <a href="users.php" class="sidebar-link"><i class="fa-solid fa-users"></i> <span class="sidebar-text">Veli & Öğrenci VT</span></a>
        <a href="appointments.php" class="sidebar-link active" style="background: rgba(91, 192, 190, 0.15); border-right: 4px solid var(--neon-blue);"><i class="fa-solid fa-calendar-check"></i> <span class="sidebar-text">Tüm Randevular</span></a>
        <a href="announcements.php" class="sidebar-link"><i class="fa-solid fa-bullhorn"></i> <span class="sidebar-text">Duyuru Yönetimi</span></a>
        <a href="blacklist.php" class="sidebar-link"><i class="fa-solid fa-user-xmark text-danger"></i> <span class="sidebar-text">Kara Liste</span></a>
        <a href="settings.php" class="sidebar-link"><i class="fa-solid fa-gears"></i> <span class="sidebar-text">Sistem Ayarları</span></a>
        <div style="position: absolute; bottom: 20px; width: 100%;">
            <a href="../../logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket text-danger"></i> <span class="sidebar-text">Güvenli Çıkış</span></a>
        </div>
    </nav>

    <div class="main-content" style="padding: 30px 50px;">
        <h4 class="text-light fw-bold mb-4"><i class="fa-solid fa-search text-warning me-2"></i>Randevu Denetim Merkezi</h4>
        <?php echo $mesaj; ?>

        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-light m-0">Tüm Randevular (<?php echo count($randevular); ?>)</h5>
                <div>
                    <button class="btn btn-sm btn-outline-info me-2 rounded-pill" onclick="window.print()"><i class="fa-solid fa-print"></i> Arşiv Çıktısı Al</button>
                    <div class="badge bg-danger">Otorite Modu Açık</div>
                </div>
            </div>

            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-dark table-hover align-middle">
                    <thead class="sticky-top bg-dark">
                        <tr>
                            <th>Tarih / Saat</th>
                            <th>Öğretmen (Branş)</th>
                            <th>Veli</th>
                            <th>Durum</th>
                            <th class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($randevular as $r): ?>
                        <tr>
                            <td>
                                <span class="text-info fw-bold"><?php echo date('d.m.Y', strtotime($r['tarih'])); ?></span><br>
                                <span class="badge bg-secondary"><?php echo $r['saat']; ?></span>
                            </td>
                            <td><?php echo htmlspecialchars(($r['ogr_ad'] ?? '').' '.($r['ogr_soyad'] ?? '')); ?><br><small class="text-muted"><?php echo htmlspecialchars($r['brans'] ?? ''); ?></small></td>
                            <td><?php echo htmlspecialchars(($r['veli_ad'] ?? '').' '.($r['veli_soyad'] ?? '')); ?></td>
                            <td>
                                <?php if($r['durum'] == 'tamamlandi'): ?>
                                    <span class="badge bg-success">Tamamlandı</span>
                                <?php elseif($r['durum'] == 'bekliyor' || $r['durum'] == 'onaylandi'): ?>
                                    <span class="badge bg-warning text-dark">Bekleniyor</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">İptal Edildi</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if($r['durum'] != 'iptal' && $r['durum'] != 'tamamlandi'): ?>
                                <a href="?iptal_et=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('İdari izin nedeniyle bu randevuyu iptal ediyorsunuz. Emin misiniz?');">
                                    <i class="fa-solid fa-ban"></i> Otorite İptali
                                </a>
                                <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled>İşlem Yok</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(count($randevular) == 0): ?>
                        <tr><td colspan="5" class="text-center text-muted">Sistemde kaydedilmiş randevu bulunmuyor.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="../../assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>

