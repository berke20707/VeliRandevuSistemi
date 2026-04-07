<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/ogretmen_data.php';

$user_id = $_SESSION['user_id'];
$ad_soyad = $_SESSION['ad_soyad'];
$aktif_ogrenci_ad = $_SESSION['aktif_ogrenci_ad'] ?? "Berke Uzun";

$aktif_randevu = null;

// Veritabanından aktif randevuyu çek
$stmt = $db->prepare("SELECT * FROM randevular WHERE veli_id = ? AND durum IN ('bekliyor', 'onaylandi') ORDER BY tarih ASC, saat ASC LIMIT 1");
$stmt->execute([$user_id]);
$aktif_randevu = $stmt->fetch(PDO::FETCH_ASSOC);

// Randevu istatistikleri
$bugun_tarih = date('Y-m-d');
$yaklasan_stmt = $db->prepare("SELECT COUNT(*) as sayi FROM randevular WHERE veli_id = ? AND durum IN ('bekliyor', 'onaylandi') AND tarih > ?");
$yaklasan_stmt->execute([$user_id, $bugun_tarih]);
$yaklasan_sayi = $yaklasan_stmt->fetch()['sayi'];

$tamamlanan_stmt = $db->prepare("SELECT COUNT(*) as sayi FROM randevular WHERE veli_id = ? AND durum = 'tamamlandi'");
$tamamlanan_stmt->execute([$user_id]);
$tamamlanan_sayi = $tamamlanan_stmt->fetch()['sayi'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Randevu Al | Ahi Evran MTAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* GLOBAL: GECE / GÜNDÜZ MODU */
        body { transition: background 0.5s ease, color 0.5s ease; }
        
        /* GÜNDÜZ MODU (Bulutlu ve Ferah) */
        body.light-mode {
            background: linear-gradient(135deg, #e0f7fa 0%, #c8e6c9 100%);
            color: #333;
        }
        body.light-mode .glass-sidebar { background: rgba(255, 255, 255, 0.7); border-right: 1px solid rgba(0,0,0,0.1); }
        body.light-mode .glass-card, 
        body.light-mode .calendar-container, 
        body.light-mode .assistant-panel,
        body.light-mode .metric-card { 
            background: rgba(255, 255, 255, 0.8) !important; 
            border: 1px solid rgba(0,0,0,0.1) !important; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        body.light-mode .text-light, body.light-mode .text-white { color: #1C2541 !important; }
        body.light-mode .text-muted { color: #6c757d !important; }
        body.light-mode .day-box { background: rgba(0,0,0,0.05); color: #333; }
        body.light-mode .day-box:hover:not(.holiday) { background: rgba(91, 192, 190, 0.2); }
        body.light-mode .mini-lesson-card { background: rgba(0,0,0,0.05); }
        body.light-mode .mini-lesson { color: #1C2541; }
        
        /* GÜNDÜZ MODU: Bulut Animasyonu */
        .cloud-wrapper { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: -2; opacity: 0; transition: opacity 1s ease; overflow: hidden; }
        body.light-mode .cloud-wrapper { opacity: 1; }
        .cloud { position: absolute; background: url('https://cdn-icons-png.flaticon.com/512/414/414927.png') no-repeat center center; background-size: contain; opacity: 0.1; animation: floatClouds linear infinite; }
        .cloud1 { width: 300px; height: 150px; top: 10%; left: -300px; animation-duration: 40s; }
        .cloud2 { width: 400px; height: 200px; top: 40%; left: -400px; animation-duration: 60s; animation-delay: -20s; }
        .cloud3 { width: 250px; height: 120px; top: 70%; left: -250px; animation-duration: 45s; animation-delay: -10s; }
        @keyframes floatClouds { 100% { transform: translateX(120vw); } }

        /* 3 KATMANLI GLOW (Randevu Al Başlığı) */
        .glow-title {
            color: #fff;
            text-shadow: 
                0 0 5px rgba(91, 192, 190, 0.8),
                0 0 15px rgba(91, 192, 190, 0.6),
                0 0 30px rgba(91, 192, 190, 0.4);
            animation: pulseGlow 2s infinite alternate;
        }
        @keyframes pulseGlow {
            0% { text-shadow: 0 0 5px rgba(91, 192, 190, 0.8), 0 0 15px rgba(91, 192, 190, 0.6), 0 0 30px rgba(91, 192, 190, 0.4); }
            100% { text-shadow: 0 0 10px rgba(91, 192, 190, 1), 0 0 25px rgba(91, 192, 190, 0.8), 0 0 45px rgba(91, 192, 190, 0.6); }
        }

        /* YILAN GİBİ DÖNEN NEON ÇİZGİ (Bugün/Seçili Gün Kutusu) */
        .day-box.today-snake { position: relative; overflow: hidden; border: none !important; }
        .day-box.today-snake::before, .day-box.today-snake::after {
            content: ''; position: absolute; width: 200%; height: 200%;
            background: conic-gradient(transparent, transparent, transparent, var(--neon-blue));
            top: -50%; left: -50%; z-index: -1; animation: rotateSnake 3s linear infinite;
        }
        .day-box.today-snake::after { animation-delay: -1.5s; }
        .day-box.today-snake .inner-box {
            position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px;
            background: #0B132B; border-radius: 8px; z-index: 0;
            display: flex; align-items: center; justify-content: center;
        }
        body.light-mode .day-box.today-snake .inner-box { background: #fff; }
        @keyframes rotateSnake { 100% { transform: rotate(360deg); } }

        /* TAKVİM AYLARI ARASI KAYMA (SLIDE) ANİMASYONLARI */
        .slide-next { animation: slideNext 0.4s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
        .slide-prev { animation: slidePrev 0.4s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
        @keyframes slideNext {
            0% { opacity: 0; transform: translateX(40px); }
            100% { opacity: 1; transform: translateX(0); }
        }
        @keyframes slidePrev {
            0% { opacity: 0; transform: translateX(-40px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        /* STANDART ÖLÇÜLERDE BİREBİR TAKVİM CSS */
        .calendar-container { background: rgba(28, 37, 65, 0.6); border-radius: 15px; padding: 25px; border: 1px solid rgba(91, 192, 190, 0.2); height: 100%; display: flex; flex-direction: column; overflow: hidden; }
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; color: #5BC0BE; font-weight: bold; font-size: 1.1rem; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; text-align: center; }
        .day-name { color: #A0B2C6; font-size: 0.85rem; font-weight: bold; margin-bottom: 10px; }
        .day-box { background: rgba(0,0,0,0.4); padding: 12px; border-radius: 10px; color: #A0B2C6; cursor: pointer; transition: all 0.2s; position: relative; font-size: 0.95rem; font-weight: bold; z-index: 1;}
        .day-box:hover:not(.holiday) { background: rgba(255,255,255,0.1); color: #fff; transform: scale(1.05); }
        .day-box.empty { visibility: hidden; }
        .day-box.holiday { background: rgba(0,0,0,0.6); color: #444; cursor: not-allowed; }
        .day-box.selected:not(.today-snake) { border: 1px solid var(--neon-blue); background: rgba(91, 192, 190, 0.15); box-shadow: 0 0 15px rgba(91, 192, 190, 0.3); color: #fff; transform: scale(1.05); z-index: 10;}
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .day-box.has-appointment { border: 2px solid #dc3545 !important; background: rgba(220, 53, 69, 0.15) !important; box-shadow: 0 0 15px rgba(220, 53, 69, 0.4) !important; color: #fff !important; transform: scale(1.05); z-index: 10; }
        .day-box.has-appointment::after { content: ''; position: absolute; top: 5px; right: 5px; width: 8px; height: 8px; background: #dc3545; border-radius: 50%; box-shadow: 0 0 5px #dc3545; }

        /* STANDART ÖLÇÜLERDE YATAY DERS PROGRAMI CSS */
        .assistant-panel { background: rgba(28, 37, 65, 0.6); border: 1px solid rgba(91, 192, 190, 0.2); border-radius: 15px; padding: 25px; height: 100%; display: flex; flex-direction: column; }
        .weekly-schedule-wrapper { display: grid; grid-template-columns: repeat(5, 1fr); gap: 5px; height: 100%; align-items: start; flex-grow: 1; }
        .day-col { background: rgba(0,0,0,0.2); border-radius: 8px; padding: 5px; display: flex; flex-direction: column; gap: 5px; height: 100%; transition: all 0.3s ease; }
        .day-col.active-day { background: rgba(91, 192, 190, 0.1); border: 1px solid rgba(91, 192, 190, 0.4); box-shadow: 0 0 15px rgba(91, 192, 190, 0.15); }
        .day-header { text-align: center; font-weight: bold; font-size: 0.75rem; color: #fff; background: rgba(91,192,190,0.2); border: 1px solid rgba(91,192,190,0.5); padding: 4px; border-radius: 4px; margin-bottom: 2px; transition: all 0.3s ease; }
        .day-col.active-day .day-header { background: rgba(91,192,190,0.4); color: #fff; box-shadow: 0 0 10px rgba(91, 192, 190, 0.3); }
        .mini-lesson-card { background: rgba(28, 37, 65, 0.6); border: 1px solid rgba(91, 192, 190, 0.15); border-radius: 4px; padding: 5px; text-align: center; transition: all 0.3s ease; position: relative; overflow: hidden; }
        .mini-lesson-card:hover { border-color: var(--neon-blue); background: rgba(91, 192, 190, 0.05); }
        .mini-lesson-card.active-lesson { background: rgba(40, 167, 69, 0.15) !important; border-color: #28a745 !important; box-shadow: 0 0 12px rgba(40, 167, 69, 0.3); animation: activeLessonPulse 2s infinite; }
        .mini-lesson-card.active-lesson::before { content: ''; position: absolute; top: 0; left: 0; width: 3px; height: 100%; background: #28a745; border-radius: 4px 0 0 4px; box-shadow: 0 0 8px #28a745; }
        @keyframes activeLessonPulse { 0%, 100% { box-shadow: 0 0 12px rgba(40, 167, 69, 0.3); } 50% { box-shadow: 0 0 20px rgba(40, 167, 69, 0.5); } }
        .mini-time { color: var(--neon-blue); font-weight: bold; display: block; margin-bottom: 2px; font-size: 0.65rem;}
        .mini-lesson { color: #fff; font-weight: bold; margin: 0; line-height: 1.1; font-size: 0.65rem; margin-bottom: 2px;}
        .mini-teacher { color: #A0B2C6; margin: 0; font-size: 0.6rem;}
        .mini-lesson-card.active-lesson .mini-time { color: #28a745; }
        .mini-lesson-card.active-lesson .mini-lesson { color: #fff; }
        .mini-lesson-card.active-lesson .mini-teacher { color: #8fffad; }

        /* İSTATİSTİK KARTLARI CSS */
        .metric-card { background: rgba(28, 37, 65, 0.6); border: 1px solid rgba(91, 192, 190, 0.2); border-radius: 12px; }
        .metric-icon-box { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }

        /* WIZARD (SEKMELİ) MODAL & BACKDROP BLUR CSS */
        .swal2-backdrop-show { backdrop-filter: blur(12px) !important; background: rgba(11, 19, 43, 0.6) !important; }
        .wizard-container { position: relative; overflow: hidden; width: 100%; }
        .wizard-step { width: 100%; transition: transform 0.4s ease-in-out; position: absolute; top: 0; left: 0; opacity: 0; visibility: hidden; }
        .wizard-step.active { position: relative; opacity: 1; visibility: visible; transform: translateX(0); }
        .wizard-step.prev { transform: translateX(-100%); }
        .wizard-step.next { transform: translateX(100%); }
        
        .modal-custom-container { text-align: left; padding-bottom: 70px; }
        .modal-teacher-scroll { display: flex; overflow-x: auto; gap: 15px; padding-bottom: 10px; scroll-snap-type: x mandatory; }
        .modal-teacher-scroll::-webkit-scrollbar { height: 8px; }
        .modal-teacher-scroll::-webkit-scrollbar-thumb { background: rgba(91, 192, 190, 0.5); border-radius: 10px; }
        .modal-mini-card { flex: 0 0 150px; scroll-snap-align: start; background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 15px 10px; text-align: center; cursor: pointer; transition: all 0.2s ease; position: relative; display: flex; flex-direction: column; align-items: center; justify-content: space-between; min-height: 150px;}
        .modal-mini-card:hover { background: rgba(91, 192, 190, 0.1); border-color: rgba(91, 192, 190, 0.5); transform: translateY(-2px); }
        .modal-mini-card.selected { background: rgba(91, 192, 190, 0.2); border-color: var(--neon-blue); box-shadow: 0 0 15px rgba(91, 192, 190, 0.4); }
        .modal-mini-card.selected::before { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; top: 8px; right: 10px; color: var(--neon-blue); font-size: 1.2rem; }
        .mmc-icon { font-size: 1.8rem; color: #A0B2C6; margin-bottom: 8px; }
        .modal-mini-card.selected .mmc-icon { color: var(--neon-blue); }
        .mmc-name { color: #fff; font-size: 0.85rem; font-weight: bold; margin-bottom: 3px; line-height: 1.2; }
        .mmc-brans { color: #A0B2C6; font-size: 0.65rem; line-height: 1.2; margin-bottom: 10px; }
        
        .time-slots-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 15px;}
        .time-btn { background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 10px; border-radius: 8px; text-align: center; cursor: pointer; transition: all 0.3s; font-weight: bold; font-size: 0.9rem; position: relative; overflow: hidden; z-index: 1;}
        .time-btn::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(45deg, var(--neon-blue), #1e3a8a); z-index: -1; opacity: 0; transition: opacity 0.3s; }
        .time-btn:hover::before { opacity: 0.5; }
        .time-btn.selected { color: #fff; border-color: transparent; box-shadow: 0 0 15px rgba(91, 192, 190, 0.5); }
        .time-btn.selected::before { opacity: 1; }

        .wizard-footer { position: absolute; bottom: 0; left: 0; width: 100%; padding: 15px 25px; background: rgba(11, 19, 43, 0.95); border-top: 1px solid rgba(91, 192, 190, 0.2); display: flex; justify-content: space-between; backdrop-filter: blur(5px); z-index: 100; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;}
        .wizard-footer .btn { font-weight: bold; padding: 8px 20px; border-radius: 20px; }
        
        .quick-tag { display: inline-block; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #A0B2C6; padding: 4px 12px; border-radius: 15px; font-size: 0.75rem; cursor: pointer; margin-right: 5px; margin-bottom: 5px; transition: 0.2s; }
        .quick-tag:hover { background: rgba(91, 192, 190, 0.2); color: var(--neon-blue); border-color: var(--neon-blue); }
        .modal-filter-box { background: rgba(0,0,0,0.2); border: 1px solid rgba(91,192,190,0.2); border-radius: 10px; padding: 10px 15px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; cursor: pointer;}

        /* FLOATING CHAT BOTU */
        .floating-chat { position: fixed; bottom: 30px; right: 30px; z-index: 1000; }
        .chat-btn { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #5BC0BE, #1e3a8a); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 5px 20px rgba(91,192,190,0.5); cursor: pointer; border: none; animation: floatChat 3s ease-in-out infinite; transition: transform 0.3s; }
        .chat-btn:hover { transform: scale(1.1); }
        @keyframes floatChat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .chat-panel { position: absolute; bottom: 80px; right: 0; width: 320px; background: rgba(11, 19, 43, 0.95); border: 1px solid rgba(91, 192, 190, 0.3); border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: none; flex-direction: column; overflow: hidden; backdrop-filter: blur(10px); opacity: 0; transform: translateY(20px); transition: all 0.3s; }
        .chat-panel.open { display: flex; opacity: 1; transform: translateY(0); }
        .chat-header { background: rgba(91, 192, 190, 0.2); padding: 15px; color: #fff; font-weight: bold; border-bottom: 1px solid rgba(91, 192, 190, 0.3); display: flex; justify-content: space-between; align-items: center; font-size: 1rem;}
        .chat-body { padding: 15px; height: 260px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; }
        .chat-msg { background: rgba(255,255,255,0.05); padding: 10px; border-radius: 10px; color: #A0B2C6; font-size: 0.85rem; line-height: 1.4; border-left: 3px solid #5BC0BE; }
        .chat-msg.user { background: rgba(91, 192, 190, 0.15); border-left: none; border-right: 3px solid #5BC0BE; align-self: flex-end; color: #fff; }
        .chat-options { display: flex; flex-direction: column; gap: 6px; padding-top: 10px;}
        .chat-opt-btn { background: transparent; border: 1px solid #5BC0BE; color: #5BC0BE; padding: 6px 10px; border-radius: 15px; font-size: 0.8rem; cursor: pointer; transition: 0.2s; text-align: left; }
        .chat-opt-btn:hover { background: #5BC0BE; color: #0B132B; }

        #spamToast { position: fixed; bottom: 20px; left: 20px; background: #dc3545; color: #fff; padding: 15px 25px; border-radius: 10px; font-weight: bold; box-shadow: 0 0 20px rgba(220,53,69,0.5); z-index: 10000; transform: translateX(-150%); transition: transform 0.3s; }
        #spamToast.show { transform: translateX(0); }
    </style>
</head>
<body class="bg-dark-space">
    <script>
        const tumOgretmenler = <?php echo json_encode($ham_kadro, JSON_UNESCAPED_UNICODE); ?>;
        const ogrencininHocalari = <?php echo json_encode($ders_programi_hocalar, JSON_UNESCAPED_UNICODE); ?>;
        const mevcutRandevu = <?php echo $aktif_randevu ? json_encode($aktif_randevu) : 'null'; ?>;
    </script>
    
    <div class="cloud-wrapper">
        <div class="cloud cloud1"></div>
        <div class="cloud cloud2"></div>
        <div class="cloud cloud3"></div>
    </div>

    <div id="spamToast"><i class="fa-solid fa-hand text-white me-2"></i> Lütfen biraz daha sakin olur musunuz?</div>
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
        <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-house"></i> <span class="sidebar-text">Ana Panel</span></a>
        <a href="randevu_al.php" class="sidebar-link active" style="background: rgba(91, 192, 190, 0.15); border-right: 4px solid var(--neon-blue);"><i class="fa-solid fa-calendar-plus"></i> <span class="sidebar-text">Randevu Al</span></a>
        <a href="ogretmenlerimiz.php" class="sidebar-link"><i class="fa-solid fa-users-viewfinder"></i> <span class="sidebar-text">Eğitim Kadrosu</span></a>
        <a href="profil.php" class="sidebar-link"><i class="fa-solid fa-user-gear"></i> <span class="sidebar-text">Profilim</span></a>
        <div style="position: absolute; bottom: 20px; width: 100%;">
            <a href="../logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket text-danger"></i> <span class="sidebar-text">Güvenli Çıkış</span></a>
        </div>
    </nav>

    <div class="main-content" style="padding: 30px 40px; overflow-x: hidden;">
        
        <div class="d-flex justify-content-between align-items-center mb-4 slide-up-fade">
            <h2 class="m-0 fw-bold glow-title" style="font-size: 1.8rem; letter-spacing: 1px;">Randevu Al</h2>
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center px-3 py-2 rounded" style="background: rgba(40, 167, 69, 0.15); border: 1px solid #28a745; color: #28a745;">
                    <i class="fa-solid fa-shield-check me-2"></i> <span class="fw-bold" style="font-size: 0.85rem;">Kara Liste: Güvenli (0/3)</span>
                </div>
                <button class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 40px; height: 40px; border-color: rgba(255,255,255,0.2); transition: all 0.3s; background: rgba(0,0,0,0.3);" onclick="toggleThemeMode()" title="Gündüz/Gece Modu">
                    <i id="theme-icon-indicator" class="fa-solid fa-moon text-light"></i>
                </button>
            </div>
        </div>

        <div class="row g-4 mb-4 slide-up-fade">
            <div class="col-md-4">
                <div class="metric-card p-3 d-flex justify-content-between align-items-center h-100">
                    <div>
                        <p class="text-white mb-1" style="font-size: 0.95rem; font-weight: 500;">Yaklaşan Randevu</p>
                        <h3 class="fw-bold m-0" style="color: #28a745;"><?php echo $yaklasan_sayi; ?></h3>
                    </div>
                    <div class="metric-icon-box" style="background: rgba(40, 167, 69, 0.15); color: #28a745;">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card p-3 d-flex justify-content-between align-items-center h-100">
                    <div>
                        <p class="text-white mb-1" style="font-size: 0.95rem; font-weight: 500;">Tamamlanan Görüşme</p>
                        <h3 class="fw-bold m-0" style="color: #ffc107;"><?php echo $tamamlanan_sayi; ?></h3>
                    </div>
                    <div class="metric-icon-box" style="background: rgba(255, 193, 7, 0.15); color: #ffc107;">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card p-3 d-flex justify-content-between align-items-center h-100">
                    <div>
                        <p class="text-white mb-1" style="font-size: 0.95rem; font-weight: 500;">Son Okul Ziyareti</p>
                        <h4 class="fw-bold m-0" style="color: #5BC0BE;">10 Mart 2026</h4>
                    </div>
                    <div class="metric-icon-box" style="background: rgba(91, 192, 190, 0.15); color: #5BC0BE;">
                        <i class="fa-solid fa-school"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4 slide-up-fade">
            <div class="col-12">
                <div id="aktifRandevuPaneli" class="glass-card p-3" style="background: rgba(91, 192, 190, 0.05); border-color: var(--neon-blue); border-left-width: 4px; <?php echo !$aktif_randevu ? 'display: none;' : ''; ?>">
                    <h6 class="text-light fw-bold mb-2"><i class="fa-solid fa-bell neon-text me-2 fa-shake"></i> Aktif Randevu Taleplerim</h6>
                    <div id="aktifRandevuKarti" class="p-3 rounded d-flex flex-column flex-lg-row justify-content-between align-items-center gap-3" style="background: rgba(0,0,0,0.4); border-left: 4px solid #5BC0BE; transition: all 0.5s;">
                        <div class="text-center text-lg-start flex-grow-1">
                            <h6 id="aktifRandevuHoca" class="text-light m-0 fw-bold"><?php echo $aktif_randevu ? "Veli Görüşmesi - " . $aktif_randevu['ogretmen_ad'] : "Görüşme Bekleniyor..."; ?></h6>
                            <p class="m-0 mt-1" style="font-size: 0.85rem; color: #A0B2C6;"><i class="fa-regular fa-calendar-days text-info me-1"></i> <span id="aktifRandevuZaman"><?php echo $aktif_randevu ? $aktif_randevu['tarih'] . ", Saat " . $aktif_randevu['saat'] : "Tarih Belirlenmedi"; ?></span></p>
                        </div>
                        <div class="text-center flex-grow-1 border-start border-end border-secondary px-3 py-1 d-none d-md-block" style="min-width: 150px;">
                            <p class="text-white m-0 mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Kalan Süre</p>
                            <h4 id="megaGeriSayim" class="fw-bold neon-text m-0" style="font-variant-numeric: tabular-nums; letter-spacing: 1px;">-- : -- : --</h4>
                        </div>
                        <div class="text-center w-100 d-md-none border-top border-secondary pt-2">
                            <p class="text-white m-0 mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Kalan Süre</p>
                            <h4 id="megaGeriSayimMobile" class="fw-bold neon-text m-0" style="font-variant-numeric: tabular-nums; letter-spacing: 1px;">-- : -- : --</h4>
                        </div>
                        <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                            <button class="btn btn-sm btn-outline-success" onclick="okulaVardimCheckIn()"><i class="fa-solid fa-street-view"></i> Okula Vardım</button>
                            <button class="btn btn-sm btn-outline-info" onclick="gecikiyorumSOS()"><i class="fa-solid fa-car-burst"></i> SOS</button>
                            <button class="btn btn-sm btn-danger" onclick="randevuIptalEt()"><i class="fa-solid fa-trash-can"></i> İptal</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4 slide-up-fade">
            
            <div class="col-lg-7">
                <div class="calendar-container">
                    <div class="d-flex justify-content-center gap-4 mb-3 pb-2" style="font-size: 0.85rem; color: #A0B2C6;">
                        <span><span class="legend-dot" style="background: #dc3545; box-shadow: 0 0 5px #dc3545;"></span> Randevunuz</span>
                        <span><span class="legend-dot" style="background: #5BC0BE; box-shadow: 0 0 5px #5BC0BE;"></span> Müsait</span>
                        <span><span class="legend-dot" style="background: #1C2541; border: 1px solid rgba(255,255,255,0.2);"></span> Kapalı</span>
                    </div>
                    
                    <div class="calendar-header">
                        <i class="fa-solid fa-chevron-left cursor-pointer fs-5" onclick="AyiDegistir(-1)"></i>
                        <span id="takvimAyi">Mart 2026</span>
                        <i class="fa-solid fa-chevron-right cursor-pointer fs-5" onclick="AyiDegistir(1)"></i>
                    </div>
                    
                    <div class="calendar-grid mb-2">
                        <div class="day-name">Pzt</div><div class="day-name">Sal</div><div class="day-name">Çar</div><div class="day-name">Per</div><div class="day-name">Cum</div><div class="day-name text-danger">Cmt</div><div class="day-name text-danger">Paz</div>
                    </div>
                    <div class="calendar-grid align-content-start flex-grow-1" id="takvimGunleri"></div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="assistant-panel">
                    <div class="text-center mb-3 pb-2" style="border-bottom: 1px solid rgba(91, 192, 190, 0.2);">
                        <h6 class="text-light fw-bold m-0"><i class="fa-solid fa-list-check neon-text me-2"></i>AMP 11/B Haftalık Program</h6>
                        <p style="color:#A0B2C6; font-size:0.75rem; margin-top:5px; margin-bottom: 0;"><i class="fa-solid fa-user-tie me-1"></i> Sınıf Öğretmeni: <b class="neon-text">Erol ALTEKİN</b></p>
                    </div>
                    <div id="dersProgramiAksi" class="weekly-schedule-wrapper"></div>
                </div>
            </div>

        </div>

        <div class="row g-4 mb-4 slide-up-fade">
            <div class="col-lg-6">
                <div class="glass-card p-3 d-flex align-items-center h-100" style="background: rgba(28, 37, 65, 0.6); border: 1px solid rgba(91, 192, 190, 0.2); border-left: 4px solid #dc3545;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background: rgba(220, 53, 69, 0.15); color: #dc3545; font-size: 1.3rem;">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>
                    <div>
                        <p class="text-white m-0 mb-1" style="font-size: 0.85rem;">En Yakın Tatile Kalan Süre</p>
                        <h5 class="text-light fw-bold m-0" id="tatilSayaci">Hesaplanıyor...</h5>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div id="canliDersKutusu" class="glass-card p-3 d-flex align-items-center h-100" style="background: rgba(28, 37, 65, 0.6); border: 1px solid rgba(91, 192, 190, 0.2); border-left: 4px solid #28a745;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 position-relative" style="width: 50px; height: 50px; background: rgba(40, 167, 69, 0.15); color: #28a745; font-size: 1.3rem;">
                        <i id="canliDersIcon" class="fa-solid fa-satellite-dish"></i>
                        <span id="canliDersDot" class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle" style="box-shadow: 0 0 10px #28a745;"><span class="visually-hidden">Canlı</span></span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="text-white m-0 mb-1" style="font-size: 0.85rem;">Şu Anki Ders (Canlı İzleme)</p>
                            <span id="canliDersKalanSure" class="badge bg-dark border border-success text-success" style="font-size: 0.7rem; font-variant-numeric: tabular-nums; display: none;"></span>
                        </div>
                        <h6 id="canliDersBilgi" class="text-success fw-bold m-0" style="font-size: 0.85rem;">Hesaplanıyor...</h6>
                        <p id="canliDersAlt" class="m-0 mt-1" style="font-size: 0.7rem; color: #A0B2C6; display: none;"></p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="floating-chat">
        <button class="chat-btn" onclick="toggleChat()"><i class="fa-solid fa-headset"></i></button>
        <div class="chat-panel" id="chatPanel">
            <div class="chat-header">
                <span><i class="fa-solid fa-robot me-2"></i> Eğitim Asistanı</span>
                <i class="fa-solid fa-times cursor-pointer fs-5" onclick="toggleChat()"></i>
            </div>
            <div class="chat-body" id="chatBody">
                <div class="chat-msg">Merhaba! Ben okulun dijital asistanıyım. Size nasıl yardımcı olabilirim? Aşağıdaki sorulardan birini seçebilirsiniz.</div>
                <div class="chat-options" id="chatOptions">
                    <button class="chat-opt-btn" onclick="botCevapla(1)">Nasıl randevu alabilirim?</button>
                    <button class="chat-opt-btn" onclick="botCevapla(2)">Randevumu nasıl iptal ederim?</button>
                    <button class="chat-opt-btn" onclick="botCevapla(3)">Kara Liste nedir?</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="../assets/js/app.js?v=<?php echo time(); ?>"></script>
    <script>
        particlesJS.load('particles-js', '../assets/js/particles.json');

        // CHAT BOT FONKSİYONLARI
        function toggleChat() { document.getElementById('chatPanel').classList.toggle('open'); }
        function botCevapla(soruId) {
            let body = document.getElementById('chatBody');
            let ops = document.getElementById('chatOptions');
            ops.style.display = 'none'; 
            
            let soruText = ""; let cevapText = "";
            if(soruId === 1) { 
                soruText = "Nasıl randevu alabilirim?"; 
                cevapText = "Çok kolay! Sayfadaki dev takvimden **müsait olan (üzerinde yazı olmayan)** bir güne tıklayın. Açılan pencerede 1. Adımda öğretmeni, 2. Adımda saati seçip 'Kaydet' butonuna basın."; 
            }
            else if(soruId === 2) { 
                soruText = "Randevumu nasıl iptal ederim?"; 
                cevapText = "Aktif bir randevunuz varsa, üstteki 'Aktif Randevu Taleplerim' panelinden kırmızı renkli **İptal** butonuna basarak iptal edebilirsiniz."; 
            }
            else if(soruId === 3) { 
                soruText = "Kara Liste nedir?"; 
                cevapText = "Aldığınız randevulara mazeretsiz olarak 3 kez katılmazsanız sistem sizi 1 ay boyunca kara listeye alır ve online randevu alamazsınız."; 
            }

            body.innerHTML += `<div class="chat-msg user">${soruText}</div>`;
            setTimeout(() => {
                body.innerHTML += `<div class="chat-msg">${cevapText}</div>`;
                body.scrollTop = body.scrollHeight; 
                setTimeout(() => { ops.style.display = 'flex'; body.appendChild(ops); body.scrollTop = body.scrollHeight; }, 1500);
            }, 500);
        }

        const aylar = ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"];
        const resmiTatiller = ["01-01", "04-23", "05-01", "05-19", "07-15", "08-30", "10-29"];
        let suAnkiTarih = new Date(); let gosterilenAy = suAnkiTarih.getMonth(); let gosterilenYil = suAnkiTarih.getFullYear();
        
        let gercekBugunStr = `${suAnkiTarih.getFullYear()}-${String(suAnkiTarih.getMonth()+1).padStart(2, '0')}-${String(suAnkiTarih.getDate()).padStart(2, '0')}`;
        let globalSeciliTarih = "";

        function TakvimOlustur(ay, yil) {
            const ilkGun = new Date(yil, ay, 1).getDay() || 7; 
            const aydakiGunSayisi = new Date(yil, ay + 1, 0).getDate();
            const bugun = new Date(); bugun.setHours(0,0,0,0);
            
            document.getElementById("takvimAyi").innerText = `${aylar[ay]} ${yil}`; let html = '';
            for (let i = 1; i < ilkGun; i++) { html += `<div class="day-box empty"></div>`; }

            for (let i = 1; i <= aydakiGunSayisi; i++) {
                let currentStr = `${yil}-${String(ay+1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                let ayGunStr = `${String(ay+1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                let gunObj = new Date(yil, ay, i); let gunHaftaninKacinciGunu = gunObj.getDay(); 
                
                let isHaftaSonu = (gunHaftaninKacinciGunu === 0 || gunHaftaninKacinciGunu === 6);
                let isResmiTatil = resmiTatiller.includes(ayGunStr);
                let isGecmis = gunObj <= bugun;

                if (isGecmis) { html += `<div class="day-box holiday" style="opacity:0.3;">${i}</div>`; continue; }
                if (isHaftaSonu || isResmiTatil) { html += `<div class="day-box holiday">${i}</div>`; continue; }

                let seciliClass = (globalSeciliTarih === currentStr) ? 'selected' : '';
                let hasAppClass = (mevcutRandevu && mevcutRandevu.tarih === currentStr) ? 'has-appointment' : '';
                
                let snakeClass = (currentStr === gercekBugunStr || currentStr === globalSeciliTarih) ? 'today-snake' : '';

                html += `<div class="day-box ${seciliClass} ${hasAppClass} ${snakeClass}" id="gun_${currentStr}" onclick="takvimGunSec('${currentStr}')"><div class="inner-box">${i}</div></div>`;
            }
            document.getElementById("takvimGunleri").innerHTML = html;
        }

        // YENİ EKLENEN KAYDIRMA ANİMASYONLU AY DEĞİŞTİRME FONKSİYONU
        function AyiDegistir(yon) { 
            const grid = document.getElementById("takvimGunleri");
            grid.classList.remove('slide-next', 'slide-prev');
            void grid.offsetWidth; // Reflow tetikle

            gosterilenAy += yon; 
            if (gosterilenAy < 0) { gosterilenAy = 11; gosterilenYil--; } 
            else if (gosterilenAy > 11) { gosterilenAy = 0; gosterilenYil++; } 
            
            TakvimOlustur(gosterilenAy, gosterilenYil); 

            // Kayma animasyonunu ekle
            if(yon > 0) {
                grid.classList.add('slide-next');
            } else {
                grid.classList.add('slide-prev');
            }
        }

        function takvimGunSec(tarih) {
            if(mevcutRandevu || document.getElementById('aktifRandevuPaneli').style.display !== 'none') {
                Swal.fire({icon: 'warning', title: 'Aktif Randevunuz Var', text: 'Mevcut bir randevunuz bulunurken yeni bir randevu alamazsınız.', background: '#1C2541', color: '#fff'});
                return;
            }

            if(globalSeciliTarih) { 
                let eskiSecim = document.getElementById('gun_' + globalSeciliTarih); 
                if(eskiSecim) { eskiSecim.classList.remove('selected'); eskiSecim.classList.remove('today-snake'); }
            }
            
            globalSeciliTarih = tarih;
            let yeniSecim = document.getElementById('gun_' + tarih); 
            if(yeniSecim) { yeniSecim.classList.add('selected'); yeniSecim.classList.add('today-snake'); }
            
            // Seçilen tarihe göre ders programında o günü highlight et
            const seciliGunKodu = getTarihGunKodu(tarih);
            if(seciliGunKodu) {
                renderDersProgrami(seciliGunKodu);
                updateCanliDers(); // Aktif ders highlight'ını yeniden uygula
            }
            
            randevuPaneliAc(tarih);
        }

        TakvimOlustur(gosterilenAy, gosterilenYil);

        // ORİJİNAL (5 SÜTUNLU) DERS PROGRAMI VERİSİ
        const dersData = {
            pzt: [{ saat: "08:30 - 15:40", ders: "Web Tasarım", hoca: "Cenk K.", hocaTam: "Cenk KARACAN" }, { saat: "15:50 - 17:20", ders: "Dijital Tasarım", hoca: "Cenk K.", hocaTam: "Cenk KARACAN" }],
            sal: [{ saat: "08:30 - 10:50", ders: "Yapay Zeka", hoca: "Erol A.", hocaTam: "Erol ALTEKİN" }, { saat: "11:00 - 13:20", ders: "Web Prog.", hoca: "Erol A.", hocaTam: "Erol ALTEKİN" }, { saat: "14:10 - 17:20", ders: "Oyun Prog.", hoca: "Erol A.", hocaTam: "Erol ALTEKİN" }],
            car: [{ saat: "08:30 - 09:10", ders: "Rehberlik", hoca: "Erol A.", hocaTam: "Erol ALTEKİN" }, { saat: "09:20 - 13:20", ders: "Mobil Uyg.", hoca: "Recep Y.", hocaTam: "Recep YAVUZ" }, { saat: "14:10 - 17:20", ders: "Grafik Can.", hoca: "Recep Y.", hocaTam: "Recep YAVUZ" }],
            per: [{ saat: "08:30 - 10:00", ders: "Felsefe", hoca: "Sena T.", hocaTam: "Sena TORLAK" }, { saat: "10:10 - 11:40", ders: "Beden", hoca: "Muhammed E.", hocaTam: "Muhammed EMRE" }, { saat: "11:50 - 13:00", ders: "Din Kültürü", hoca: "Züleyha U.", hocaTam: "Züleyha ULAŞ" }, { saat: "13:50 - 15:20", ders: "İngilizce", hoca: "Ahmet G.", hocaTam: "Ahmet GÜNARSLAN" }],
            cum: [{ saat: "08:30 - 10:00", ders: "Tarih", hoca: "Sinem E.", hocaTam: "Sinem ERTAŞ" }, { saat: "10:10 - 13:20", ders: "Edebiyat", hoca: "Vildan H.", hocaTam: "Vildan HAYKIR" }, { saat: "14:10 - 14:50", ders: "Sağlık Trafik", hoca: "Ahmet A.", hocaTam: "Ahmet AKIN" }]
        };

        const gunIsimleri = { pzt: "Pazartesi", sal: "Salı", car: "Çarşamba", per: "Perşembe", cum: "Cuma" };
        const gunIndexToKod = { 1: 'pzt', 2: 'sal', 3: 'car', 4: 'per', 5: 'cum' };

        // Saat string'ini dakikaya çevir ("08:30" -> 510)
        function saatToDakika(saatStr) {
            const [h, m] = saatStr.trim().split(':').map(Number);
            return h * 60 + m;
        }

        // Bugünün gün kodunu al
        function getBugununGunKodu() {
            const gunIndex = new Date().getDay(); // 0=Pazar, 1=Pzt, ..., 6=Cmt
            return gunIndexToKod[gunIndex] || null;
        }

        // Verilen tarih string'inden (YYYY-MM-DD) gün kodunu al
        function getTarihGunKodu(tarihStr) {
            const d = new Date(tarihStr);
            const gunIndex = d.getDay();
            return gunIndexToKod[gunIndex] || null;
        }

        // Şu anki dersi bul (gerçek zamanlı)
        function getCurrentLesson() {
            const simdi = new Date();
            const gunKodu = getBugununGunKodu();
            if (!gunKodu) return null; // Hafta sonu

            const dersler = dersData[gunKodu];
            if (!dersler) return null;

            const simdiDakika = simdi.getHours() * 60 + simdi.getMinutes();

            for (let i = 0; i < dersler.length; i++) {
                const d = dersler[i];
                const saatParts = d.saat.split(' - ');
                const basla = saatToDakika(saatParts[0]);
                const bitis = saatToDakika(saatParts[1]);

                if (simdiDakika >= basla && simdiDakika < bitis) {
                    return { ...d, index: i, gunKodu, baslaDk: basla, bitisDk: bitis, simdiDk: simdiDakika };
                }
            }
            return null;
        }

        // Bir sonraki dersi bul
        function getNextLesson() {
            const simdi = new Date();
            const gunKodu = getBugununGunKodu();
            if (!gunKodu) return null;

            const dersler = dersData[gunKodu];
            if (!dersler) return null;

            const simdiDakika = simdi.getHours() * 60 + simdi.getMinutes();

            for (let i = 0; i < dersler.length; i++) {
                const d = dersler[i];
                const saatParts = d.saat.split(' - ');
                const basla = saatToDakika(saatParts[0]);
                if (simdiDakika < basla) {
                    return { ...d, index: i, gunKodu, baslaDk: basla };
                }
            }
            return null;
        }

        // Dakikayı "SS:DD" formatına çevir
        function dakikaToSaat(dk) {
            const h = Math.floor(dk / 60);
            const m = dk % 60;
            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
        }

        // ========= CANLI DERS İZLEME SİSTEMİ =========
        function updateCanliDers() {
            const bilgiEl = document.getElementById('canliDersBilgi');
            const altEl = document.getElementById('canliDersAlt');
            const sureEl = document.getElementById('canliDersKalanSure');
            const kutuEl = document.getElementById('canliDersKutusu');
            const dotEl = document.getElementById('canliDersDot');
            if (!bilgiEl) return;

            const gunKodu = getBugununGunKodu();

            // Hafta sonu kontrolü
            if (!gunKodu) {
                bilgiEl.innerHTML = '<i class="fa-solid fa-bed me-1"></i> Hafta sonu — Ders yok';
                bilgiEl.className = 'fw-bold m-0';
                bilgiEl.style.cssText = 'font-size: 0.85rem; color: #A0B2C6;';
                kutuEl.style.borderLeftColor = '#6c757d';
                dotEl.className = 'position-absolute top-0 start-100 translate-middle p-1 bg-secondary border border-light rounded-circle';
                dotEl.style.boxShadow = '0 0 10px #6c757d';
                sureEl.style.display = 'none';
                altEl.style.display = 'none';
                return;
            }

            const mevcutDers = getCurrentLesson();

            if (mevcutDers) {
                // DERS VAR — CANLI
                const kalanDk = mevcutDers.bitisDk - mevcutDers.simdiDk;
                const kalanSaat = Math.floor(kalanDk / 60);
                const kalanDakika = kalanDk % 60;
                const kalanStr = kalanSaat > 0 ? `${kalanSaat}s ${kalanDakika}dk` : `${kalanDakika}dk`;

                bilgiEl.innerHTML = `Öğrenciniz şu an <b class="text-light">${mevcutDers.ders}</b> dersinde`;
                bilgiEl.className = 'text-success fw-bold m-0';
                bilgiEl.style.cssText = 'font-size: 0.85rem;';

                altEl.innerHTML = `<i class="fa-solid fa-chalkboard-user me-1"></i>${mevcutDers.hocaTam} &bull; ${mevcutDers.saat}`;
                altEl.style.display = 'block';

                sureEl.innerHTML = `<i class="fa-solid fa-clock me-1"></i>${kalanStr} kaldı`;
                sureEl.style.display = 'inline-block';

                kutuEl.style.borderLeftColor = '#28a745';
                dotEl.className = 'position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle';
                dotEl.style.boxShadow = '0 0 10px #28a745';

                // Ders programında aktif dersi highlight et
                document.querySelectorAll('.mini-lesson-card').forEach(el => el.classList.remove('active-lesson'));
                const aktifKart = document.querySelector(`[data-gun="${mevcutDers.gunKodu}"][data-ders-index="${mevcutDers.index}"]`);
                if (aktifKart) aktifKart.classList.add('active-lesson');

            } else {
                // DERS YOK — Sonraki dersi kontrol et
                document.querySelectorAll('.mini-lesson-card').forEach(el => el.classList.remove('active-lesson'));
                const sonraki = getNextLesson();

                if (sonraki) {
                    const simdiDk = new Date().getHours() * 60 + new Date().getMinutes();
                    const kalanDk = sonraki.baslaDk - simdiDk;
                    const kalanStr = kalanDk > 60 ? `${Math.floor(kalanDk/60)}s ${kalanDk%60}dk` : `${kalanDk}dk`;

                    bilgiEl.innerHTML = `<i class="fa-solid fa-hourglass-half me-1"></i> Teneffüs — Sonraki: <b class="text-light">${sonraki.ders}</b>`;
                    bilgiEl.className = 'fw-bold m-0';
                    bilgiEl.style.cssText = 'font-size: 0.85rem; color: #ffc107;';

                    altEl.innerHTML = `<i class="fa-solid fa-chalkboard-user me-1"></i>${sonraki.hocaTam} &bull; ${sonraki.saat}`;
                    altEl.style.display = 'block';

                    sureEl.innerHTML = `<i class="fa-solid fa-bell me-1"></i>${kalanStr} sonra`;
                    sureEl.className = 'badge bg-dark border border-warning text-warning';
                    sureEl.style.cssText = 'font-size: 0.7rem; font-variant-numeric: tabular-nums; display: inline-block;';

                    kutuEl.style.borderLeftColor = '#ffc107';
                    dotEl.className = 'position-absolute top-0 start-100 translate-middle p-1 bg-warning border border-light rounded-circle';
                    dotEl.style.boxShadow = '0 0 10px #ffc107';
                } else {
                    // Dersler bitti veya henüz başlamadı
                    const simdiDk = new Date().getHours() * 60 + new Date().getMinutes();
                    const ilkDers = dersData[gunKodu][0];
                    const ilkBasla = saatToDakika(ilkDers.saat.split(' - ')[0]);

                    if (simdiDk < ilkBasla) {
                        const kalanDk = ilkBasla - simdiDk;
                        const kalanStr = kalanDk > 60 ? `${Math.floor(kalanDk/60)}s ${kalanDk%60}dk` : `${kalanDk}dk`;
                        bilgiEl.innerHTML = `<i class="fa-solid fa-sun me-1"></i> Dersler henüz başlamadı`;
                        bilgiEl.className = 'fw-bold m-0';
                        bilgiEl.style.cssText = 'font-size: 0.85rem; color: #17a2b8;';
                        altEl.innerHTML = `İlk ders: <b>${ilkDers.ders}</b> — ${ilkDers.saat}`;
                        altEl.style.display = 'block';
                        sureEl.innerHTML = `<i class="fa-solid fa-clock me-1"></i>${kalanStr} sonra`;
                        sureEl.className = 'badge bg-dark border border-info text-info';
                        sureEl.style.cssText = 'font-size: 0.7rem; font-variant-numeric: tabular-nums; display: inline-block;';
                        kutuEl.style.borderLeftColor = '#17a2b8';
                        dotEl.className = 'position-absolute top-0 start-100 translate-middle p-1 bg-info border border-light rounded-circle';
                        dotEl.style.boxShadow = '0 0 10px #17a2b8';
                    } else {
                        bilgiEl.innerHTML = `<i class="fa-solid fa-flag-checkered me-1"></i> Bugünkü dersler tamamlandı`;
                        bilgiEl.className = 'fw-bold m-0';
                        bilgiEl.style.cssText = 'font-size: 0.85rem; color: #A0B2C6;';
                        altEl.innerHTML = `Yarınki programı takvimden kontrol edebilirsiniz`;
                        altEl.style.display = 'block';
                        sureEl.style.display = 'none';
                        kutuEl.style.borderLeftColor = '#6c757d';
                        dotEl.className = 'position-absolute top-0 start-100 translate-middle p-1 bg-secondary border border-light rounded-circle';
                        dotEl.style.boxShadow = '0 0 10px #6c757d';
                    }
                }
            }
        }

        // ========= DERS PROGRAMI RENDER (AKTİF GÜN HIGHLIGHT) =========
        function renderDersProgrami(highlightGunKodu) {
            const container = document.getElementById('dersProgramiAksi');
            container.innerHTML = '';
            const bugunKodu = getBugununGunKodu();
            const activeGun = highlightGunKodu || bugunKodu;

            for (const [gunKod, gunAd] of Object.entries(gunIsimleri)) {
                const isActive = (gunKod === activeGun);
                let html = `<div class="day-col ${isActive ? 'active-day' : ''}" data-gun-col="${gunKod}">`;
                html += `<div class="day-header">${isActive ? '<i class="fa-solid fa-location-dot me-1" style="font-size:0.6rem;"></i>' : ''}${gunAd}</div>`;
                dersData[gunKod].forEach((d, idx) => {
                    html += `<div class="mini-lesson-card" data-gun="${gunKod}" data-ders-index="${idx}"><span class="mini-time">${d.saat}</span><p class="mini-lesson">${d.ders}</p><p class="mini-teacher">${d.hoca}</p></div>`;
                });
                html += `</div>`;
                container.innerHTML += html;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderDersProgrami(null);
            updateCanliDers();
            setInterval(updateCanliDers, 10000); // Her 10 saniyede güncelle

            // Otomatik Popup Açma (dashboard'dan gelenler için)
            const urlParams = new URLSearchParams(window.location.search);
            const tarihParam = urlParams.get('tarih');
            const otoAcParam = urlParams.get('otomatik_ac');
            if(tarihParam && otoAcParam === '1') {
                setTimeout(() => {
                    takvimGunSec(tarihParam);
                }, 400); 
            }
        });

        // =================================================================
        // WIZARD SEKMELİ MODAL SİSTEMİ
        // =================================================================
        let modalSeciliHocaAd = ""; let modalSeciliSaat = ""; let modalSadeceKendiHocalari = false;
        let currentStep = 1;

        function randevuPaneliAc(tarih) {
            modalSeciliHocaAd = ""; modalSeciliSaat = ""; modalSadeceKendiHocalari = false; currentStep = 1;
            let tarihObj = new Date(tarih); let gun = tarihObj.getDate(); let ay = aylar[tarihObj.getMonth()]; let formatliTarih = `${gun} ${ay} ${tarihObj.getFullYear()}`;

            Swal.fire({
                title: '<span style="color:#5BC0BE;"><i class="fa-regular fa-calendar-check"></i> Yeni Randevu Oluştur</span>',
                background: 'rgba(28, 37, 65, 0.95)', color: '#fff', width: '750px',
                html: `
                    <div class="modal-custom-container" id="wizardContainer">
                        <div class="alert py-2 px-3 mb-4 text-center" style="font-size:0.95rem; color:#5BC0BE; background:rgba(91, 192, 190, 0.1); border: 1px solid #5BC0BE; border-radius: 10px;">
                            <i class="fa-solid fa-calendar-day me-2"></i> Seçilen Tarih: <b class="text-white">${formatliTarih}</b>
                        </div>
                        
                        <div class="wizard-container" style="min-height: 350px;">
                            <div class="wizard-step active" id="step1">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="text-info fw-bold m-0"><i class="fa-solid fa-1 me-2"></i>Öğretmen Seçin</h5>
                                </div>
                                <div class="modal-filter-box" onclick="toggleModalFilter()">
                                    <span class="text-light" style="font-size: 0.85rem;"><i class="fa-solid fa-child-reaching me-2 text-info"></i>Çocuğumun dersine giren öğretmenleri listele</span>
                                    <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" id="modalFilterToggle" style="pointer-events: none;"></div>
                                </div>
                                <div id="modalTeacherGridContainer" class="modal-teacher-scroll"></div>
                            </div>

                            <div class="wizard-step next" id="step2">
                                <h5 class="text-info fw-bold mb-3"><i class="fa-solid fa-2 me-2"></i>Saat ve Gündem</h5>
                                <div class="time-slots-grid">
                                    <div class="time-btn" id="time_10:00" onclick="modalSaatSec('10:00')">10:00</div><div class="time-btn" id="time_11:00" onclick="modalSaatSec('11:00')">11:00</div>
                                    <div class="time-btn" id="time_12:00" onclick="modalSaatSec('12:00')">12:00</div><div class="time-btn" id="time_13:00" onclick="modalSaatSec('13:00')">13:00</div>
                                    <div class="time-btn" id="time_14:00" onclick="modalSaatSec('14:00')">14:00</div><div class="time-btn" id="time_15:00" onclick="modalSaatSec('15:00')">15:00</div>
                                    <div class="time-btn" id="time_16:00" onclick="modalSaatSec('16:00')">16:00</div><div class="time-btn" id="time_17:00" onclick="modalSaatSec('17:00')">17:00</div>
                                </div>
                                <label class="text-info fw-bold mb-2 mt-3"><i class="fa-solid fa-pen-clip me-1"></i> Görüşme Nedeni (Opsiyonel)</label>
                                <div class="mb-2">
                                    <span class="quick-tag" onclick="hizliNotEkle('Sınav Notları')"><i class="fa-solid fa-plus me-1"></i>Sınav Notları</span>
                                    <span class="quick-tag" onclick="hizliNotEkle('Devamsızlık Durumu')"><i class="fa-solid fa-plus me-1"></i>Devamsızlık</span>
                                    <span class="quick-tag" onclick="hizliNotEkle('Davranış')"><i class="fa-solid fa-plus me-1"></i>Davranış</span>
                                </div>
                                <textarea id="panel_not" class="form-control bg-dark text-light border-info" rows="2" maxlength="250" placeholder="Eklemek istediğiniz notu yazın..." style="border-radius: 8px; resize: none;"></textarea>
                            </div>
                        </div>

                        <div class="wizard-footer">
                            <button type="button" class="btn btn-outline-secondary" id="btnIptalGeri" onclick="wizardBackOrCancel()">İptal</button>
                            <button type="button" class="btn btn-info text-dark" id="btnIleriKaydet" onclick="wizardNextOrSave('${tarih}', '${formatliTarih}')">İleri <i class="fa-solid fa-arrow-right ms-1"></i></button>
                        </div>
                    </div>`,
                showConfirmButton: false, showCancelButton: false,
                didOpen: () => { modalOgretmenleriCiz(false); }
            });
        }

        function wizardNextOrSave(hamTarih, formatliTarih) {
            if(currentStep === 1) {
                if(!modalSeciliHocaAd) { Swal.showValidationMessage(`Lütfen bir öğretmen seçin!`); return; }
                Swal.resetValidationMessage();
                document.getElementById('step1').classList.remove('active'); document.getElementById('step1').classList.add('prev');
                document.getElementById('step2').classList.remove('next'); document.getElementById('step2').classList.add('active');
                document.getElementById('btnIptalGeri').innerHTML = `<i class="fa-solid fa-arrow-left me-1"></i> Geri`;
                document.getElementById('btnIleriKaydet').innerHTML = `<i class="fa-solid fa-check me-1"></i> Kaydet`;
                document.getElementById('btnIleriKaydet').classList.remove('btn-info'); document.getElementById('btnIleriKaydet').classList.add('btn-success');
                currentStep = 2;
            } else if(currentStep === 2) {
                if(!modalSeciliSaat) { Swal.showValidationMessage(`Lütfen bir saat seçin!`); return; }
                
                // AJAX ile veritabanına kaydet
                let gundemNotu = document.getElementById('panel_not') ? document.getElementById('panel_not').value : '';
                let kaydetBtn = document.getElementById('btnIleriKaydet');
                kaydetBtn.disabled = true;
                kaydetBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin me-1"></i> Kaydediliyor...`;

                fetch('../api/randevu_kaydet.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ogretmen_ad: modalSeciliHocaAd,
                        tarih: hamTarih,
                        saat: modalSeciliSaat,
                        gundem: gundemNotu
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire({ 
                            icon: 'success', 
                            title: 'Harika!', 
                            html: `<p>Randevunuz başarıyla oluşturuldu ve sistem güncelleniyor...</p><small style="color:#A0B2C6;">Öğretmen: <b>${data.randevu.ogretmen_ad}</b><br>Tarih: <b>${formatliTarih}</b> — Saat: <b>${modalSeciliSaat}</b></small>`, 
                            background: '#1C2541', color: '#fff', confirmButtonColor: '#5BC0BE' 
                        }).then(() => {
                            window.location.href = 'randevu_al.php';
                        });
                    } else {
                        kaydetBtn.disabled = false;
                        kaydetBtn.innerHTML = `<i class="fa-solid fa-check me-1"></i> Kaydet`;
                        Swal.showValidationMessage(data.message);
                    }
                })
                .catch(err => {
                    kaydetBtn.disabled = false;
                    kaydetBtn.innerHTML = `<i class="fa-solid fa-check me-1"></i> Kaydet`;
                    Swal.showValidationMessage('Bağlantı hatası. Lütfen tekrar deneyin.');
                    console.error('Randevu kaydetme hatası:', err);
                });
            }
        }

        function wizardBackOrCancel() {
            if(currentStep === 2) {
                Swal.resetValidationMessage();
                document.getElementById('step2').classList.remove('active'); document.getElementById('step2').classList.add('next');
                document.getElementById('step1').classList.remove('prev'); document.getElementById('step1').classList.add('active');
                document.getElementById('btnIptalGeri').innerHTML = `İptal`;
                document.getElementById('btnIleriKaydet').innerHTML = `İleri <i class="fa-solid fa-arrow-right ms-1"></i>`;
                document.getElementById('btnIleriKaydet').classList.remove('btn-success'); document.getElementById('btnIleriKaydet').classList.add('btn-info');
                currentStep = 1;
            } else {
                Swal.close(); 
                if(globalSeciliTarih) { let sec = document.getElementById('gun_' + globalSeciliTarih); if(sec && !sec.classList.contains('has-appointment')) { sec.classList.remove('selected'); sec.classList.remove('today-snake'); globalSeciliTarih = ""; } }
            }
        }

        function toggleModalFilter() { modalSadeceKendiHocalari = !modalSadeceKendiHocalari; document.getElementById('modalFilterToggle').checked = modalSadeceKendiHocalari; modalOgretmenleriCiz(modalSadeceKendiHocalari); }
        
        function modalOgretmenleriCiz(sadeceKendi) {
            let container = document.getElementById('modalTeacherGridContainer'); let html = ""; let gosterilecekIndex = 0;
            tumOgretmenler.forEach((hoca, idx) => {
                if(hoca.tur !== 'ogretmen') return; 
                let isOwn = ogrencininHocalari.includes(hoca.ad); if(sadeceKendi && !isOwn) return;
                let seciliClass = (modalSeciliHocaAd === hoca.ad) ? "selected" : ""; let ikonRenk = isOwn ? "color: #5BC0BE;" : ""; 
                html += `<div class="modal-mini-card ${seciliClass}" id="mmc_${idx}" onclick="modalHocaSec(${idx}, '${hoca.ad.replace(/'/g, "\\'")}')">
                        <div class="mmc-icon" style="${ikonRenk}"><i class="fa-solid fa-chalkboard-user"></i></div><div class="mmc-name">${hoca.ad}</div>
                        <div class="mmc-brans">${hoca.brans.replace('Öğretmeni', 'Öğrt.')}</div><button type="button" class="mmc-btn">${seciliClass ? 'Seçildi' : 'Seç'}</button></div>`;
                gosterilecekIndex++;
            });
            if(gosterilecekIndex === 0) { html = `<div class="text-muted p-3" style="width:100%; text-align:center;">Öğretmen bulunamadı.</div>`; }
            container.innerHTML = html;
        }

        function modalHocaSec(idx, hocaAd) {
            modalSeciliHocaAd = hocaAd; document.querySelectorAll('.modal-mini-card').forEach(el => { el.classList.remove('selected'); el.querySelector('.mmc-btn').innerText = 'Seç'; });
            let secilenKart = document.getElementById('mmc_' + idx); if(secilenKart) { secilenKart.classList.add('selected'); secilenKart.querySelector('.mmc-btn').innerText = 'Seçildi'; }
        }

        function modalSaatSec(saatStr) {
            modalSeciliSaat = saatStr; document.querySelectorAll('.time-btn').forEach(el => el.classList.remove('selected'));
            let secilenBtn = document.getElementById('time_' + saatStr); if(secilenBtn) { secilenBtn.classList.add('selected'); }
        }

        function hizliNotEkle(notText) { let textarea = document.getElementById('panel_not'); if(textarea.value.length > 0) { textarea.value += ", " + notText; } else { textarea.value = notText; } }

        function okulaVardimCheckIn() {
            Swal.fire({ title: 'Okula Vardınız Mı?', text: 'Öğretmene ve güvenliğe geldiğiniz bildirilecek.', background: '#1C2541', color: '#fff', showCancelButton: true, confirmButtonText: 'Evet, Geldim' }).then((r) => { 
                if(r.isConfirmed) { 
                    fetch('../api/randevu_aksiyon.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ islem: 'vardim' }) })
                    .then(res => res.json()).then(data => {
                        if(data.success) Swal.fire({icon: 'success', title: 'Check-in!', html: `Öğretmen ve güvenliğe bildirildi.`, background: '#1C2541', color: '#fff'});
                    });
                } 
            }); 
        }

        function gecikiyorumSOS() { 
            Swal.fire({ title: 'Gecikiyor Musunuz?', text: 'Öğretmene 15 dakika kadar gecikeceğiniz iletilecek.', background: '#1C2541', color: '#fff', showCancelButton: true, confirmButtonText: 'Evet, Bildir' }).then((r) => { 
                if(r.isConfirmed) { 
                    fetch('../api/randevu_aksiyon.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ islem: 'sos' }) })
                    .then(res => res.json()).then(data => {
                        if(data.success) Swal.fire({icon: 'info', title: 'SOS Gönderildi', text: '15 dakika gecikeceğiniz başarıyla bildirildi.', background: '#1C2541', color: '#fff'});
                    });
                } 
            }); 
        }

        function randevuIptalEt() { Swal.fire({ title: '<span style="color:#dc3545;">Randevu İptali</span>', html: `<select class="form-select bg-dark text-light border-danger" id="iptalNeden"><option>Acil işim çıktı</option><option>Sağlık problemi</option><option>Ulaşım sorunu</option><option>Diğer</option></select>`, background: '#1C2541', color: '#fff', showCancelButton: true, confirmButtonText: 'Evet, İptal Et', confirmButtonColor: '#dc3545' }).then((r) => { if(r.isConfirmed) { fetch('../api/randevu_iptal.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({}) }).then(res => res.json()).then(data => { if(data.success) { Swal.fire({ icon: 'info', title: 'İptal Edildi', text: 'Randevunuz veritabanından başarıyla silindi.', background: '#1C2541', color: '#fff' }).then(() => { window.location.reload(); }); } else { Swal.fire({ icon: 'error', title: 'Hata', text: data.message, background: '#1C2541', color: '#fff' }); } }).catch(err => { Swal.fire({ icon: 'error', title: 'Bağlantı Hatası', text: 'İptal işlemi sırasında bir hata oluştu.', background: '#1C2541', color: '#fff' }); }); } }); }

        function updateMegaTimer() {
            let elDesktop = document.getElementById('megaGeriSayim'); let elMobile = document.getElementById('megaGeriSayimMobile');
            if(!elDesktop && !elMobile) return;
            if(!mevcutRandevu) { if(elDesktop) elDesktop.innerText = "-- : -- : --"; if(elMobile) elMobile.innerText = "-- : -- : --"; return; }
            let ts = mevcutRandevu.saat; let rt = new Date(mevcutRandevu.tarih + "T" + (ts.length === 5 ? ts + ":00" : ts)); let f = rt - new Date();
            if (f <= 0) { let expiredText = "00 : 00 : 00"; if(elDesktop) { elDesktop.innerText = expiredText; elDesktop.classList.remove('neon-text'); elDesktop.classList.add('text-danger'); } if(elMobile) { elMobile.innerText = expiredText; elMobile.classList.remove('neon-text'); elMobile.classList.add('text-danger'); } return; }
            let g = Math.floor(f / (1000 * 60 * 60 * 24)); let s = Math.floor((f % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)); let d = Math.floor((f % (1000 * 60 * 60)) / (1000 * 60)); let sn = Math.floor((f % (1000 * 60)) / 1000);
            let displayText = (g > 0 ? g + "G " : "") + String(s).padStart(2,'0') + " : " + String(d).padStart(2,'0') + " : " + String(sn).padStart(2,'0');
            if(elDesktop) elDesktop.innerText = displayText; if(elMobile) elMobile.innerText = displayText;
        }
        setInterval(updateMegaTimer, 1000); updateMegaTimer();

        function updateTatilTimer() {
            let ts = document.getElementById('tatilSayaci'); if(!ts) return;
            let s = new Date(); let y = s.getFullYear(); let arr = [];
            resmiTatiller.forEach(t => arr.push(new Date(y + "-" + t)));
            resmiTatiller.forEach(t => arr.push(new Date((y+1) + "-" + t)));
            arr.sort((a,b) => a - b);
            let nxt = arr.find(d => d > s);
            if(nxt) {
                let f = nxt - s; let g = Math.floor(f / 86400000); let h = Math.floor((f % 86400000) / 3600000);
                ts.innerText = g + " Gün " + h + " Saat";
            }
        }
        updateTatilTimer(); setInterval(updateTatilTimer, 60000);
    </script>
</body>
</html>