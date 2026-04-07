<?php
// Hataları ekranda görmek için (Sadece geliştirme aşamasında açık olmalı)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$dbname = 'veli_randevu_sistemi';
$username = 'root'; // XAMPP/WAMP kullanıyorsan genelde root'tur
$password = ''; // XAMPP/WAMP kullanıyorsan genelde boştur

try {
    // PDO bağlantısını oluşturuyoruz ve Türkçe karakter sorunu olmaması için utf8mb4 ayarlıyoruz
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // PDO hata modunu Exception (İstisna) fırlatacak şekilde ayarlıyoruz
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Veritabanından çekilen verilerin her zaman ilişkisel dizi (associative array) olarak gelmesini sağlıyoruz
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // Eğer bağlantı başarısız olursa, havalı ve modern bir AntiGravity hata ekranı tasarlayana kadar şimdilik düz metin basıyoruz
    die("Veritabanı Bağlantı Hatası: Sistem şu an uzay boşluğunda süzülüyor. Lütfen daha sonra tekrar deneyin.");
}
?>