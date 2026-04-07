<?php
$css = "
@media print {
    body, .bg-dark-space { background: #fff !important; color: #000 !important; }
    .glass-sidebar, .btn, .badge, #particles-js, .dropdown, .school-watermark { display: none !important; }
    .main-content, .glass-card { margin: 0 !important; padding: 0 !important; border: none !important; background: transparent !important; }
    .table, .table-dark { color: #000 !important; background: #fff !important; border-color: #000 !important; }
    .table th { border-bottom: 2px solid #000 !important; color: #000 !important; background: #fff !important;}
    .table td { border-bottom: 1px solid #ccc !important; background: #fff !important; }
    .text-light, .text-warning, .text-info, .text-success, .text-danger { color: #000 !important; }
    .neon-text { text-shadow: none !important; color: #000 !important; }
    .table-responsive { overflow: visible !important; }
}
";
file_put_contents('assets/css/style.css', $css, FILE_APPEND);
echo "Done";
