<?php
require_once __DIR__ . '/../autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Only POST allowed', 405);

$code = '';
$lang = $_POST['lang'] ?? 'auto';
$filename = $_POST['filename'] ?? '';
$outputMode = $_POST['output_mode'] ?? 'auto';
$pipelineJson = $_POST['pipeline'] ?? '[]';
$level = intval($_POST['level'] ?? 0);
$nestCount = min(max(intval($_POST['nest'] ?? 1), 1), 5);

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $code = file_get_contents($_FILES['file']['tmp_name']);
    $filename = $_FILES['file']['name'];
} else {
    $code = $_POST['code'] ?? '';
}

if (empty($code)) sendError('No code provided');

$originalSize = strlen($code);

$pipeline = json_decode($pipelineJson, true);
if (!is_array($pipeline)) $pipeline = [];

$allSteps = [];
$allWarnings = [];
$allSuggestions = [];
for ($round = 0; $round < $nestCount; $round++) {
    if ($round === 0 && !empty($pipeline)) {
        $validation = PipelineValidator::validate($pipeline, $lang);
        $allWarnings = array_merge($allWarnings, $validation['warnings'] ?? []);
        $allSuggestions = array_merge($allSuggestions, $validation['suggestions'] ?? []);
    }
    $engine = new CrypterEngine($code, $lang, $filename);
    if (!empty($pipeline)) {
        $engine->setPipeline($pipeline);
    } elseif ($level > 0) {
        $engine->setLevel($level);
    } else {
        $engine->setLevel(3);
    }
    $result = $engine->execute();
    $code = $result['code'];
    $lang = $result['language'];
    $allSteps = array_merge($allSteps, array_map(function($s) use ($round) {
        $s['round'] = $round + 1;
        return $s;
    }, $result['steps']));
}

$outputSize = strlen($code);
$useFileDownload = $outputSize > 50000 || $outputMode === 'file';

$response = [
    'success' => true,
    'code' => $code,
    'language' => $result['language'],
    'original_size' => $originalSize,
    'final_size' => $outputSize,
    'expansion_ratio' => $result['expansion_ratio'],
    'entropy' => $result['entropy'],
    'steps' => $allSteps,
    'rounds' => $nestCount,
    'download' => $useFileDownload,
    'mime' => getMimeType($result['language']),
    'ext' => getExtension($result['language']),
    'warnings' => $allWarnings,
    'suggestions' => $allSuggestions,
];

sendJson($response);

function getMimeType($lang) {
    $map = [
        'python' => 'text/x-python', 'javascript' => 'text/javascript',
        'typescript' => 'text/javascript', 'php' => 'text/x-php',
        'ruby' => 'text/x-ruby', 'perl' => 'text/x-perl',
        'bash' => 'text/x-shellscript', 'batch' => 'text/x-msdos-batch',
        'powershell' => 'text/x-powershell', 'lua' => 'text/x-lua',
        'r' => 'text/x-r', 'go' => 'text/x-go', 'rust' => 'text/x-rust',
        'java' => 'text/x-java', 'csharp' => 'text/x-csharp',
        'kotlin' => 'text/x-kotlin', 'swift' => 'text/x-swift',
        'dart' => 'text/x-dart', 'scala' => 'text/x-scala',
        'haskell' => 'text/x-haskell', 'elixir' => 'text/x-elixir',
        'clojure' => 'text/x-clojure', 'groovy' => 'text/x-groovy',
    ];
    return $map[$lang] ?? 'text/plain';
}

function getExtension($lang) {
    $map = [
        'python' => 'py', 'javascript' => 'js', 'typescript' => 'ts',
        'php' => 'php', 'ruby' => 'rb', 'perl' => 'pl',
        'bash' => 'sh', 'batch' => 'bat', 'powershell' => 'ps1',
        'lua' => 'lua', 'r' => 'r', 'go' => 'go', 'rust' => 'rs',
        'java' => 'java', 'csharp' => 'cs', 'kotlin' => 'kt',
        'swift' => 'swift', 'dart' => 'dart', 'scala' => 'scala',
        'haskell' => 'hs', 'elixir' => 'ex', 'clojure' => 'clj',
        'groovy' => 'groovy',
    ];
    return $map[$lang] ?? 'txt';
}
