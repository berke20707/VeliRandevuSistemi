<?php
require 'config/database.php';
$tc = 'serkanakdag';
$p = 'sakdag123';
$hash = password_hash($p, PASSWORD_DEFAULT);

// Güncelle veya yeni oluştur
$check = $db->query("SELECT id FROM users WHERE rol='yonetici' LIMIT 1")->fetchColumn();
if($check) {
    $db->prepare("UPDATE users SET tc_kimlik=?, kullanici_adi=?, sifre=?, adi='Serkan', soyadi='AKDAĞ' WHERE id=?")->execute([$tc, $tc, $hash, $check]);
    echo "UPDATED id=$check | tc=$tc | pass=$p | hash=$hash";
} else {
    $db->prepare("INSERT INTO users (adi,soyadi,kullanici_adi,tc_kimlik,sifre,rol,email,cinsiyet) VALUES ('Serkan','AKDAĞ',?,?,?,'yonetici','mudur@ahievran.edu.tr','Erkek')")->execute([$tc, $tc, $hash]);
    echo "INSERTED";
}
@unlink(__FILE__);
