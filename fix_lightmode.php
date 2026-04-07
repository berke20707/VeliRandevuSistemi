<?php
$css = "
/* ============================================================ */
/* 🌟 ULTIMATE LIGHT MODE OVERRIDES (PREMIUM APPLE-STYLE) 🌟 */
/* ============================================================ */
body.light-mode {
    background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%) !important;
    background-color: #fdfbfb !important;
    color: #2c3e50 !important;
}

/* KARTLAR VE CAM TASARIMLAR (BEYAZ/AÇIK GRİ FROSTED GLASS) */
body.light-mode .glass-card,
body.light-mode .glass-sidebar,
body.light-mode .calendar-container,
body.light-mode .assistant-panel,
body.light-mode .metric-card {
    background: rgba(255, 255, 255, 0.85) !important;
    border: 1px solid rgba(0, 0, 0, 0.1) !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
    color: #2c3e50 !important;
    backdrop-filter: blur(15px) !important;
}

/* YAZILAR (HEADINGS, LABELS, PARAGRAPHS) */
body.light-mode .text-light,
body.light-mode .text-white,
body.light-mode h1, body.light-mode h2, body.light-mode h3, 
body.light-mode h4, body.light-mode h5, body.light-mode h6,
body.light-mode .sidebar-text,
body.light-mode .profile-label,
body.light-mode .form-label {
    color: #1a252f !important;
}

body.light-mode .text-muted,
body.light-mode .lesson-teacher {
    color: #636e72 !important;
}

/* TABLOLAR */
body.light-mode .table-custom-dark {
    color: #2d3436 !important;
}
body.light-mode .table-custom-dark thead th {
    background: rgba(0, 0, 0, 0.05) !important;
    color: #2c3e50 !important;
    border-bottom: 2px solid rgba(0, 0, 0, 0.1) !important;
}
body.light-mode .table-custom-dark tbody tr {
    background: rgba(255, 255, 255, 0.6) !important;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
}
body.light-mode .table-custom-dark tbody tr:hover {
    background: rgba(0, 0, 0, 0.03) !important;
}

/* FORMLAR, INPUTLAR VE SELECT KUTULARI */
body.light-mode .form-control,
body.light-mode .form-select,
body.light-mode .custom-setting-input,
body.light-mode input[type='text'],
body.light-mode input[type='password'],
body.light-mode input[type='email'],
body.light-mode input[type='file'],
body.light-mode textarea {
    background: #ffffff !important;
    border: 1px solid #ced4da !important;
    color: #2d3436 !important;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.05) !important;
}
body.light-mode .form-control:focus,
body.light-mode .custom-setting-input:focus {
    border-color: #5BC0BE !important;
    box-shadow: 0 0 0 0.2rem rgba(91, 192, 190, 0.25) !important;
    background: #ffffff !important;
}
body.light-mode input::placeholder,
body.light-mode textarea::placeholder {
    color: #a4b0be !important;
}

/* MODALLAR (AÇILIR PENCERELER) */
body.light-mode .modal-content {
    background: #ffffff !important;
    border: 1px solid rgba(0,0,0,0.1) !important;
    box-shadow: 0 15px 50px rgba(0,0,0,0.15) !important;
    color: #2d3436 !important;
}
body.light-mode .modal-header,
body.light-mode .modal-footer {
    border-color: rgba(0,0,0,0.08) !important;
    background: rgba(248, 249, 250, 0.9) !important;
}
body.light-mode .modal-header h5,
body.light-mode .modal-header .btn-close {
    color: #2c3e50 !important;
}
body.light-mode .profile-line {
    border-bottom: 1px solid rgba(0,0,0,0.05) !important;
    color: #2d3436 !important;
}
body.light-mode .profile-line span:first-child {
    color: #636e72 !important;
}

/* BUTONLAR VE İKONLAR */
body.light-mode .btn-close {
    filter: invert(1) grayscale(100%) brightness(0) !important;
}
body.light-mode .neon-blue, body.light-mode .neon-text {
    color: #0984e3 !important;
    text-shadow: none !important;
}
body.light-mode .sidebar-link:hover {
    background: rgba(0, 0, 0, 0.05) !important;
    color: #0984e3 !important;
}

/* TAKVİM VE DERS PROGRAMI EKLENTİSİ OVERRIDES */
body.light-mode .day-box {
    background: #ffffff;
    color: #2c3e50;
    border: 1px solid rgba(0,0,0,0.1);
}
body.light-mode .day-box:hover:not(.holiday) { background: #e3f2fd !important; color: #0984e3 !important;}
body.light-mode .day-box.holiday { background: #f8f9fa !important; color: #b2bec3 !important; }
body.light-mode .day-box.has-appointment { background: rgba(220, 53, 69, 0.1) !important; border-color: #dc3545 !important; color: #dc3545 !important;}
body.light-mode .mini-lesson-card { background: #fdfdfd !important; border-color: rgba(0,0,0,0.1) !important;}
body.light-mode .mini-lesson-card:hover { background: #e3f2fd !important; border-color: #0984e3 !important;}
body.light-mode .mini-time { color: #0984e3 !important; }
body.light-mode .mini-lesson { color: #2d3436 !important; }
body.light-mode .mini-teacher { color: #636e72 !important; }

/* ETİKETLER (BADGES) */
body.light-mode .badge.bg-secondary { background: #dfe6e9 !important; color: #2d3436 !important;}
body.light-mode .badge.bg-dark { background: #f1f2f6 !important; color: #2f3542 !important; border-color: #ced6e0 !important;}

/* SİSTEM BİLDİRİM ZİLİ DROPDOWN */
body.light-mode .dropdown-menu { background: #ffffff !important; border: 1px solid rgba(0,0,0,0.1) !important; box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important; }
body.light-mode .dropdown-item { color: #2d3436 !important; }
body.light-mode .dropdown-item:hover { background: #f8f9fa !important; }
body.light-mode .dropdown-header { color: #636e72 !important; border-bottom: 1px solid rgba(0,0,0,0.05) !important; }
";
file_put_contents('assets/css/style.css', $css, FILE_APPEND);
echo "Extensive Light Mode Appended!";
