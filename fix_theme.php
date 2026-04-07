<?php
$files = [
    'pages/student/okulumuz.php',
    'pages/student/dashboard.php',
    'pages/randevu_al.php',
    'pages/ogrenci_sec.php',
    'pages/admin/dashboard.php'
];

foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $content = file_get_contents($f);
    
    // Regex ile inline toggleThemeMode fonksiyonunu tamamen sil
    $pattern = '/\/\/\s*TEMA DEĞİŞTİRME[^\n]*\n\s*function toggleThemeMode\(\)\s*\{.*?\n\s*\}/s';
    $content = preg_replace($pattern, '', $content, -1, $count1);
    
    // Yorum satırı olmadan başlıyanı da yakala
    $pattern2 = '/function toggleThemeMode\(\)\s*\{.*?icon\.style\.color\s*=\s*.+?;\s*\n\s*\}/s';
    $content = preg_replace($pattern2, '', $content, -1, $count2);
    
    // Hâlâ kaldıysa daha genel bir regex
    $pattern3 = '/function toggleThemeMode\(\)\s*\{[^\}]+\}/s';
    $content = preg_replace($pattern3, '', $content, -1, $count3);
    
    if ($count1 || $count2 || $count3) {
        file_put_contents($f, $content);
        echo "Removed from $f\n";
    }
}

// Ensure app.js handles DOMContentLoaded correctly for the icon
$appjs = file_get_contents('assets/js/app.js');
file_put_contents('assets/js/app.js', str_replace('localStorage.getItem(\'themeMode\') || \'dark\'', 'localStorage.getItem(\'themeMode\') === \'light\' ? \'light\' : \'dark\'', $appjs));

echo "Done\n";
