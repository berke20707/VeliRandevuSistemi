<?php
$files = ['pages/admin/teachers.php', 'pages/admin/users.php', 'pages/admin/settings.php'];
foreach ($files as $f) {
    if(!file_exists($f)) continue;
    $content = file_get_contents($f);
    
    // Check if particles is inside the HTML body normally
    if (strpos($content, 'particles.min.js') === false) {
        $scripts = "<script src=\"https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js\"></script>\n    <script src=\"../../assets/js/app.js?v=<?php echo time(); ?>\"></script>\n</body>";
        $content = str_replace('</body>', $scripts, $content);
        file_put_contents($f, $content);
        echo "Fixed $f\n";
    }
}
echo "Done\n";
