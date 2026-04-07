<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. GİRİŞ KONTROLÜ
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// 2. ÖZELLİK: 15 DAKİKA (900 Saniye) HAREKETSİZLİK KONTROLÜ (TIMEOUT)
if (isset($_SESSION['son_islem_zamani']) && (time() - $_SESSION['son_islem_zamani'] > 900)) {
    // 15 dakika geçmiş, oturumu imha et!
    session_unset();
    session_destroy();
    // Kullanıcıyı login sayfasına timeout parametresiyle at
    header("Location: ../login.php?timeout=1");
    exit;
}

// Kullanıcı her tıkladığında / sayfayı yenilediğinde sayacı sıfırla
$_SESSION['son_islem_zamani'] = time();
?>