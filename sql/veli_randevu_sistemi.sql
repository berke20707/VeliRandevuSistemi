-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 07 Nis 2026, 11:56:37
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `veli_randevu_sistemi`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `hedef` varchar(50) DEFAULT 'hepsi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `veli_id` int(11) NOT NULL,
  `ogretmen_id` int(11) NOT NULL,
  `tarih_saat` datetime NOT NULL,
  `durum` enum('bekliyor','onaylandi','iptal','ertelendi') DEFAULT 'bekliyor',
  `veli_on_not` text DEFAULT NULL,
  `ogretmen_gizli_not` text DEFAULT NULL,
  `olusturulma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `blacklist`
--

CREATE TABLE `blacklist` (
  `id` int(11) NOT NULL,
  `tc_kimlik` varchar(11) NOT NULL,
  `ad_soyad` varchar(100) DEFAULT NULL,
  `sebep` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `branches`
--

INSERT INTO `branches` (`id`, `name`) VALUES
(7, 'Beden Eğitimi'),
(1, 'Bilişim Teknolojileri'),
(9, 'Elektrik-Elektronik'),
(3, 'Fizik'),
(8, 'İngilizce'),
(4, 'Kimya'),
(10, 'Makine Teknolojisi'),
(11, 'Matematik'),
(6, 'Tarih'),
(5, 'Türk Dili ve Edebiyatı');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `baslik` varchar(100) NOT NULL,
  `mesaj` text NOT NULL,
  `okundu_mu` tinyint(1) DEFAULT 0,
  `olusturulma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ogrenciler`
--

CREATE TABLE `ogrenciler` (
  `id` int(11) NOT NULL,
  `adi` varchar(50) NOT NULL,
  `soyadi` varchar(50) NOT NULL,
  `sinif` varchar(20) NOT NULL,
  `okul_no` varchar(20) NOT NULL,
  `tc_kimlik` varchar(11) NOT NULL,
  `telefon` varchar(15) DEFAULT NULL,
  `veli_adi` varchar(100) NOT NULL,
  `veli_telefon` varchar(15) NOT NULL,
  `sifre` varchar(255) NOT NULL,
  `kayit_tarihi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `ogrenciler`
--

INSERT INTO `ogrenciler` (`id`, `adi`, `soyadi`, `sinif`, `okul_no`, `tc_kimlik`, `telefon`, `veli_adi`, `veli_telefon`, `sifre`, `kayit_tarihi`) VALUES
(9, 'Berke', 'UZUN', 'AMP 11/B', '907', '12345678901', '544 255 82 42', 'Ercan UZUN', '542 657 92 49', '$2y$10$0dXAUua/rKGyoDO20lCo3.1q6Ok9qMjV0INqiwPJ1EtXkwXf2TfRa', '2026-03-25 02:59:14');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `randevular`
--

CREATE TABLE `randevular` (
  `id` int(11) NOT NULL,
  `veli_id` int(11) NOT NULL,
  `veli_ad_soyad` varchar(100) DEFAULT NULL,
  `ogrenci_ad` varchar(100) DEFAULT NULL,
  `ogrenci_sinif` varchar(20) DEFAULT NULL,
  `ogretmen_ad` varchar(100) NOT NULL,
  `tarih` date NOT NULL,
  `saat` varchar(10) NOT NULL,
  `gundem` text DEFAULT NULL,
  `durum` enum('bekliyor','onaylandi','ogretmen_iptal','veli_iptal','tamamlandi') DEFAULT 'bekliyor',
  `olusturulma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `randevular`
--

INSERT INTO `randevular` (`id`, `veli_id`, `veli_ad_soyad`, `ogrenci_ad`, `ogrenci_sinif`, `ogretmen_ad`, `tarih`, `saat`, `gundem`, `durum`, `olusturulma_tarihi`) VALUES
(5, 9, 'Ercan UZUN', 'Berke UZUN', 'AMP 11/B', 'Cenk KARACAN', '2026-04-08', '11:00', 'Sınav Notları', '', '2026-04-07 08:38:15');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) DEFAULT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'appointment_hours', '09:00, 09:50, 10:40, 11:30, 12:20, 13:50, 14:40'),
(2, 'holidays', '2026-01-01, 2026-04-23, 2026-05-01, 2026-05-19, 2026-07-15, 2026-08-30, 2026-10-29');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `brans` varchar(50) NOT NULL,
  `ogle_arasi_baslangic` time DEFAULT NULL,
  `ogle_arasi_bitis` time DEFAULT NULL,
  `nobetci_gunler` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `adi` varchar(50) NOT NULL,
  `soyadi` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `tc_kimlik` varchar(11) DEFAULT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `acil_telefon` varchar(20) DEFAULT NULL,
  `kan_grubu` varchar(10) DEFAULT NULL,
  `hatirlatici_zamani` varchar(20) DEFAULT '1_gun',
  `iki_adimli_dogrulama` tinyint(1) DEFAULT 0,
  `hesap_donduruldu` tinyint(1) DEFAULT 0,
  `kullanici_adi` varchar(50) NOT NULL,
  `cinsiyet` enum('Erkek','Kadın','Belirtmek İstemiyorum') NOT NULL,
  `sifre` varchar(255) NOT NULL,
  `rol` enum('veli','ogretmen','yonetici') DEFAULT 'veli',
  `brans` varchar(50) DEFAULT NULL,
  `profil_resmi` varchar(255) DEFAULT 'default.png',
  `email_onayli` tinyint(1) DEFAULT 0,
  `onay_kodu` varchar(100) DEFAULT NULL,
  `hatali_giris_sayisi` int(11) DEFAULT 0,
  `hesap_kilit_suresi` datetime DEFAULT NULL,
  `hatirla_token` varchar(255) DEFAULT NULL,
  `olusturulma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `silindi_mi` tinyint(1) DEFAULT 0,
  `silinme_tarihi` datetime DEFAULT NULL,
  `son_guvenli_cikis` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `adi`, `soyadi`, `email`, `tc_kimlik`, `telefon`, `acil_telefon`, `kan_grubu`, `hatirlatici_zamani`, `iki_adimli_dogrulama`, `hesap_donduruldu`, `kullanici_adi`, `cinsiyet`, `sifre`, `rol`, `brans`, `profil_resmi`, `email_onayli`, `onay_kodu`, `hatali_giris_sayisi`, `hesap_kilit_suresi`, `hatirla_token`, `olusturulma_tarihi`, `silindi_mi`, `silinme_tarihi`, `son_guvenli_cikis`) VALUES
(9, 'Ercan', 'UZUN', 'berke20707@gmail.com', '12345678902', '542 657 92 49', NULL, NULL, '1_gun', 0, 0, '', 'Erkek', '$2y$10$HYAsuF88JXf.gkUgaty9peyQQAimzSzvLMLivkFINaNPMDtEyY7/m', 'veli', NULL, 'fa-user-tie', 0, NULL, 0, NULL, NULL, '2026-03-24 23:58:39', 0, NULL, 1),
(11, 'Serkan', 'AKDAĞ', 'mudur@ahievran.edu.tr', 'serkanakdag', NULL, NULL, NULL, '1_gun', 0, 0, 'serkanakdag', 'Erkek', '$2y$10$8H8cXOmQmblHxWaFnoIsieuhnJZEkp6xGmaxEy5XDKPRt92eoaAb2', 'yonetici', NULL, 'default.png', 0, NULL, 0, NULL, NULL, '2026-04-07 08:16:49', 0, NULL, 1),
(12, 'Ercan', 'Uzun', 'ercan@gmail.com', '11111111111', NULL, NULL, NULL, '1_gun', 0, 0, '11111111111', 'Erkek', '$2y$10$DdSpvRu9jnI497PT8jRvZOK9Yd6LIxYIQ6E9DyfHr735d0sN/7eeK', 'ogretmen', 'Bilişim Teknolojileri', 'default.png', 0, NULL, 0, NULL, NULL, '2026-04-07 08:42:38', 1, NULL, 1),
(13, 'Erol', 'ALTEKİN', 'erolaltekin@gmail.com', 'erolaltekin', NULL, NULL, NULL, '1_gun', 0, 0, 'erolaltekin', 'Erkek', '$2y$10$BkwG3Xq6gHz7r/HfXz6Rougb6IqxVBp9JExh2oY3G/mp0E7KU2Jv.', 'ogretmen', 'Bilişim Teknolojileri', 'fa-chalkboard-user', 0, NULL, 0, NULL, NULL, '2026-04-07 09:30:30', 0, NULL, 1);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `veli_id` (`veli_id`),
  ADD KEY `ogretmen_id` (`ogretmen_id`);

--
-- Tablo için indeksler `blacklist`
--
ALTER TABLE `blacklist`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Tablo için indeksler `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `ogrenciler`
--
ALTER TABLE `ogrenciler`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `okul_no` (`okul_no`),
  ADD UNIQUE KEY `tc_kimlik` (`tc_kimlik`);

--
-- Tablo için indeksler `randevular`
--
ALTER TABLE `randevular`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Tablo için indeksler `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `kullanici_adi` (`kullanici_adi`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `blacklist`
--
ALTER TABLE `blacklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `ogrenciler`
--
ALTER TABLE `ogrenciler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Tablo için AUTO_INCREMENT değeri `randevular`
--
ALTER TABLE `randevular`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`veli_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`ogretmen_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
