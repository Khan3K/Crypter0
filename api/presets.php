<?php
require_once __DIR__ . '/../autoload.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$level = intval($_GET['level'] ?? 3);
$lang = $_GET['lang'] ?? 'auto';

$level = min(max($level, 1), 12);
$profile = ProtectionProfile::getForLang($level, $lang);
echo json_encode($profile);
