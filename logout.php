<?php
session_start();
require_once 'config/database.php';

// Güvenli çıkış yapıldıysa veritabanına işle
if (isset($_SESSION['user_id'])) {
    $db->prepare("UPDATE users SET son_guvenli_cikis = 1 WHERE id = ?")->execute([$_SESSION['user_id']]);
}

session_unset();
session_destroy();
header("Location: login.php");
exit;
?>