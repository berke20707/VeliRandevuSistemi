<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['rol'] = 'yonetici';
$_SESSION['ad_soyad'] = 'Serkan AKDAG';
chdir('pages/admin');
include('dashboard.php');
