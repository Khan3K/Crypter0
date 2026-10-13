<?php
require_once __DIR__ . '/../autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Only POST allowed', 405);

$code = $_POST['code'] ?? '';
$lang = $_POST['lang'] ?? 'python';
$filename = $_POST['filename'] ?? '';

if (empty($code)) sendError('No code provided');

$extensions = ['python' => 'py', 'javascript' => 'js', 'typescript' => 'ts', 'php' => 'php', 'ruby' => 'rb', 'perl' => 'pl', 'bash' => 'sh', 'batch' => 'bat', 'powershell' => 'ps1', 'lua' => 'lua', 'r' => 'r', 'go' => 'go', 'rust' => 'rs', 'csharp' => 'cs', 'java' => 'java', 'kotlin' => 'kt', 'scala' => 'scala', 'swift' => 'swift', 'dart' => 'dart', 'haskell' => 'hs', 'elixir' => 'ex', 'clojure' => 'clj', 'groovy' => 'groovy'];
$ext = $extensions[$lang] ?? 'txt';
$name = $filename ? pathinfo($filename, PATHINFO_FILENAME) . '_crypted.' . $ext : 'crypted_output.' . $ext;

header('Content-Type: text/plain; charset=utf-8');
$name = str_replace(['"', "\r", "\n"], ["'", '', ''], $name);
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Content-Length: ' . strlen($code));
echo $code;
