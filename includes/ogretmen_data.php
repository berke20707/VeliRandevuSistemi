<?php
// AMP 11/B DERS PROGRAMI ÖĞRETMENLERİ (Güncel listeye göre eşlendi)
$ders_programi_hocalar = [
    'Cenk KARACAN', 'Erol ALTEKİN', 'Recep YAVUZ', 
    'Sena TORLAK', 'Muhammed EMRE', /* Beden Eğitimi Güncellendi */
    'Züleyha ULAŞ', 'Ahmet GÜNARSLAN', 'Sinem ERTAŞ', 
    'Vildan HAYKIR', /* Edebiyat Güncellendi */ 'Ahmet AKIN'
];

$ham_kadro = [
    // İDARE
    ['ad' => 'Serkan AKDAĞ', 'brans' => 'Müdür', 'tur' => 'mudur', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Hakkı AĞDAĞ', 'brans' => 'Müdür Yardımcısı', 'tur' => 'mudur_yardimcisi', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Canan ERKUT', 'brans' => 'Müdür Yardımcısı', 'tur' => 'mudur_yardimcisi', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Sıla ÖNBEY', 'brans' => 'Müdür Yardımcısı', 'tur' => 'mudur_yardimcisi', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Nazım TÜZÜMET', 'brans' => 'Müdür Yardımcısı', 'tur' => 'mudur_yardimcisi', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Özge YILMAZ TÜZÜMET', 'brans' => 'Müdür Yardımcısı', 'tur' => 'mudur_yardimcisi', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Betül AYDIN GENÇ', 'brans' => 'Psikolojik Danışman ve Rehber Öğretmen', 'tur' => 'mudur_yardimcisi', 'okulda' => true, 'nobet_gunu' => 'Yok'],

    // BİLİŞİM
    ['ad' => 'Meltem CESUR', 'brans' => 'Bilişim Teknolojileri Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Ertan GÜNEŞ', 'brans' => 'Bilişim Teknolojileri Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Çarşamba'], 
    ['ad' => 'Emrah UZUN', 'brans' => 'Bilişim Teknolojileri Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Tuğba UZUN AK', 'brans' => 'Bilişim Teknolojileri Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Barış SADIKOĞLU', 'brans' => 'Bilişim Teknolojileri Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Cuma'],
    ['ad' => 'Müjgan AVCIOĞULLARI', 'brans' => 'Bilişim Teknolojileri Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Cenk KARACAN', 'brans' => 'Bilişim Teknolojileri Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Salı'],
    ['ad' => 'Emine BAYSAL', 'brans' => 'Bilişim Teknolojileri Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Erol ALTEKİN', 'brans' => 'Bilişim Teknolojileri Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'İbrahim HALAVURT', 'brans' => 'Bilişim Teknolojileri Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Recep YAVUZ', 'brans' => 'Bilişim Teknolojileri Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Melike GÜNEŞ', 'brans' => 'Bilişim Teknolojileri Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Ali Hadi ÖZKIR', 'brans' => 'Bilişim Teknolojileri Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Pazartesi'],

    // ELEKTRİK
    ['ad' => 'Onur ÖZ', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Alan Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Cem MERDİN', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Hasan ÇOLAKOĞLU', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'İlker DOĞANAY', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'İsmail TURA', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Recep ARINÇ', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Şükriye DOĞANAY', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Serkan ÇIBIKÇI', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Erkan ÖZKAN', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Erkan ÖZYILMAZ', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Turhan ÇAVAÇ', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Fatih CEBECİ', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Halil KESİM', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Hatice Kübra ULUDAĞ', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Vural DİNDAR', 'brans' => 'Elektrik Elektronik Teknolojisi Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],

    // OTOMASYON
    ['ad' => 'Ammar KAYA', 'brans' => 'Endüstriyel Otomasyon Teknolojileri Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Halil İbrahim GÜRBÜZ', 'brans' => 'Endüstriyel Otomasyon Teknolojileri Alanı Alan Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Yusuf KURT', 'brans' => 'Endüstriyel Otomasyon Teknolojileri Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Salih KIR', 'brans' => 'Endüstriyel Otomasyon Teknolojileri Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],

    // MOTOR
    ['ad' => 'Mehmet KESKİN', 'brans' => 'Motorlu Araçlar Teknolojisi Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Serkan TÖNGELCİ', 'brans' => 'Motorlu Araçlar Teknolojisi Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Taner KEREM', 'brans' => 'Motorlu Araçlar Teknolojisi Alanı Atölye Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Sami TÜKEK', 'brans' => 'Motorlu Araçlar Teknolojisi Alanı Alan Şefi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Adem CAN', 'brans' => 'Motorlu Araçlar Teknolojisi Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Emre ÖZTÜRK', 'brans' => 'Motorlu Araçlar Teknolojisi Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Erkan AKKOYUNLU', 'brans' => 'Motorlu Araçlar Teknolojisi Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Yasin BARUT', 'brans' => 'Motorlu Araçlar Teknolojisi Alanı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],

    // KÜLTÜR DERSLERİ
    ['ad' => 'Ahmet AKIN', 'brans' => 'Biyoloji Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Abdulkerim KUŞMAN', 'brans' => 'Biyoloji Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Merve MARANGOZ', 'brans' => 'Biyoloji Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Gamze GÜLEN', 'brans' => 'Coğrafya Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Burcu MEZGİL ARICI', 'brans' => 'Coğrafya Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Sena TORLAK', 'brans' => 'Felsefe Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Feriha AYDEMİR', 'brans' => 'Fizik Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Kadir ÖZÜPAK', 'brans' => 'Fizik Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Şerife DÖKME', 'brans' => 'Fizik Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Ahmet GÜNARSLAN', 'brans' => 'İngilizce Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Haşim NASIRLI', 'brans' => 'İngilizce Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Aylin ŞEN', 'brans' => 'İngilizce Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Başak BABACAN', 'brans' => 'İngilizce Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Ece EROĞLU', 'brans' => 'Kimya Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Elif KÜÇÜKÇAKAN', 'brans' => 'Kimya Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Ebru ÖZDEMİR CAN', 'brans' => 'Matematik Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Enes ÖZEL', 'brans' => 'Matematik Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Sezgi GAZİ TUNA', 'brans' => 'Matematik Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Fulya HASANÇEBİOĞLU', 'brans' => 'Matematik Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Handan KARAHAN TAŞDEMİR', 'brans' => 'Matematik Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Nevin GÜNEY', 'brans' => 'Matematik Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Zübeyde TUĞLU GÜNAŞTI', 'brans' => 'Matematik Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Mehmet ÇAKIR', 'brans' => 'Tarih Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Sinem ERTAŞ', 'brans' => 'Tarih Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Çağla NACAR', 'brans' => 'Tarih Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Ayşe Gül ÖZİÇ', 'brans' => 'Tarih Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Özlem YILMAZ ORAK', 'brans' => 'Tarih Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Emine BAŞARAN', 'brans' => 'Türk Dili ve Edebiyatı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Habib ÇAPLI', 'brans' => 'Türk Dili ve Edebiyatı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Name AKINCI', 'brans' => 'Türk Dili ve Edebiyatı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Zehra ŞENAD', 'brans' => 'Türk Dili ve Edebiyatı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Vildan HAYKIR', 'brans' => 'Türk Dili ve Edebiyatı Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Feyzanur KAYAN', 'brans' => 'Din Kültürü ve Ahlak Bilgisi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Nafiye Yeşil ALAGÖZ', 'brans' => 'Din Kültürü ve Ahlak Bilgisi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Züleyha ULAŞ', 'brans' => 'Din Kültürü ve Ahlak Bilgisi', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'Muhammed EMRE', 'brans' => 'Beden Eğitimi ve Spor Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok'],
    ['ad' => 'İsmail İSTİM', 'brans' => 'Beden Eğitimi ve Spor Öğretmeni', 'tur' => 'ogretmen', 'okulda' => true, 'nobet_gunu' => 'Yok']
];

// YENİ TASARIM İÇİN KATEGORİLEME (Müdür, Müdür Yardımcıları ve Zümreler ayrıldı)
$mudur = [];
$mudur_yardimcilari = [];
$gruplar = [];

foreach($ham_kadro as $h) {
    // Öğrencinin hocası mı kontrolü
    $h['kendi_hocasi'] = in_array($h['ad'], $ders_programi_hocalar) ? true : false;

    if($h['tur'] == 'mudur') {
        $mudur[] = $h;
    } 
    elseif($h['tur'] == 'mudur_yardimcisi') {
        $mudur_yardimcilari[] = $h;
    } 
    else {
        $brans = $h['brans'];
        if(strpos($brans, 'Bilişim') !== false) { $gruplar['Bilişim Teknolojileri Bölümü'][] = $h; }
        elseif(strpos($brans, 'Elektrik') !== false) { $gruplar['Elektrik Elektronik Teknolojisi Bölümü'][] = $h; }
        elseif(strpos($brans, 'Otomasyon') !== false) { $gruplar['Endüstriyel Otomasyon Bölümü'][] = $h; }
        elseif(strpos($brans, 'Motor') !== false) { $gruplar['Motorlu Araçlar Teknolojisi Bölümü'][] = $h; }
        else {
            $grup_adi = str_replace(' Öğretmeni', ' Öğretmenleri', $brans);
            $gruplar[$grup_adi][] = $h;
        }
    }
}
ksort($gruplar);

// İSİM BAŞ HARFİ ÇIKARTMA FONKSİYONU
if(!function_exists('getInitials')) {
    function getInitials($name) {
        $parts = explode(' ', trim($name));
        if(count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1, 'UTF-8') . mb_substr(end($parts), 0, 1, 'UTF-8'), 'UTF-8');
        }
        return mb_strtoupper(mb_substr($name, 0, 2, 'UTF-8'), 'UTF-8');
    }
}
?>