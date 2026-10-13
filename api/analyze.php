<?php
require_once __DIR__ . '/../autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Only POST allowed', 405);

$code = $_POST['code'] ?? '';
if (empty($code)) sendError('No code provided');

$lang = detectLanguage($code);
$lines = substr_count($code, "\n") + 1;
$size = strlen($code);
$charFreq = count_chars($code, 1);
$entropy = 0;
if ($size > 0) {
    foreach ($charFreq as $count) { $p = $count / $size; $entropy -= $p * log($p, 256); }
}

$suggestions = [];
switch ($lang) {
    case 'python':
        $suggestions = ['base64', 'xor', 'aes', 'marshal', 'variable_rename', 'dead_code', 'anti_debug'];
        break;
    case 'javascript':
        $suggestions = ['base64', 'xor', 'function_wrap', 'iife', 'variable_rename', 'dead_code'];
        break;
    case 'php':
        $suggestions = ['base64', 'xor', 'aes', 'gzinflate', 'variable_vars', 'dead_code'];
        break;
    default:
        $suggestions = ['base64', 'xor', 'variable_rename', 'dead_code'];
}

sendJson([
    'success' => true,
    'language' => $lang,
    'lines' => $lines,
    'size' => $size,
    'entropy' => round($entropy, 4),
    'suggestions' => $suggestions,
]);
