<?php
require 'config/database.php';
$stmt = $db->query("DESCRIBE randevular");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
