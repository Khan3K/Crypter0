<?php
spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/src/';
    $prefixes = ['core', 'languages', 'techniques', 'utils'];
    foreach ($prefixes as $prefix) {
        $file = $baseDir . $prefix . '/' . $class . '.php';
        if (file_exists($file)) { require $file; return; }
    }
});

function sendJson($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function sendError($msg, $code = 400) {
    sendJson(['error' => $msg], $code);
}

function detectLanguage($code, $filename = '') {
    $extMap = [
        'py' => 'python', 'js' => 'javascript', 'ts' => 'typescript', 'tsx' => 'typescript',
        'jsx' => 'javascript', 'mjs' => 'javascript', 'cjs' => 'javascript',
        'php' => 'php', 'phtml' => 'php',
        'rb' => 'ruby', 'erb' => 'ruby', 'rake' => 'ruby',
        'pl' => 'perl', 'pm' => 'perl',
        'sh' => 'bash', 'bash' => 'bash', 'zsh' => 'bash', 'fish' => 'bash',
        'bat' => 'batch', 'cmd' => 'batch',
        'ps1' => 'powershell', 'psm1' => 'powershell',
        'lua' => 'lua',
        'r' => 'r', 'R' => 'r', 'rmd' => 'r',
        'go' => 'go',
        'rs' => 'rust',
        'cs' => 'csharp', 'csx' => 'csharp',
        'java' => 'java', 'kt' => 'kotlin', 'kts' => 'kotlin',
        'swift' => 'swift',
        'dart' => 'dart',
        'scala' => 'scala', 'sc' => 'scala',
        'hs' => 'haskell', 'lhs' => 'haskell',
        'ml' => 'ocaml', 'mli' => 'ocaml',
        'erl' => 'erlang', 'hrl' => 'erlang',
        'ex' => 'elixir', 'exs' => 'elixir',
        'clj' => 'clojure', 'cljs' => 'clojure', 'cljc' => 'clojure',
        'groovy' => 'groovy', 'gvy' => 'groovy',
        'cr' => 'crystal',
        'nim' => 'nim', 'nimble' => 'nim',
        'zig' => 'zig',
        'd' => 'd',
        'f' => 'fortran', 'for' => 'fortran', 'f90' => 'fortran', 'f95' => 'fortran', 'f03' => 'fortran',
        'sql' => 'sql',
        'html' => 'html', 'htm' => 'html', 'xhtml' => 'html',
        'css' => 'css', 'scss' => 'css', 'sass' => 'css', 'less' => 'css',
        'asm' => 'assembly', 's' => 'assembly', 'S' => 'assembly',
    ];
    if ($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (isset($extMap[$ext])) return $extMap[$ext];
    }
    $signatures = [
        '/^\s*(import|from)\s+\w+|def\s+\w+\s*\(|class\s+\w+[\s:(]|print\s*\(|__name__|__main__|lambda\s/' => 'python',
        '/^\s*(const|let|var)\s+\w+\s*[=:]|function\s+\w+\s*\(|=>\s*{|console\.\w+\(|require\s*\(|module\.exports|import\s+.*from|export\s+(default|const|function|class)/' => 'javascript',
        '/^\s*(const|let|var|interface|type|enum)\s+\w+.*:\s*(string|number|boolean|any|void|never|unknown)|import\s+type\s|export\s+type\s/' => 'typescript',
        '/^\s*<\?php|namespace\s+\w+|use\s+[\w\\]+|function\s+\w+\s*\([^)]*\)\s*:\s*\w|echo\s+|\$[a-zA-Z_]/' => 'php',
        '/^\s*def\s+\w+|class\s+\w+\s*(<|implements)|require\s|include\s|puts\s|print\s|attr_accessor|module\s+\w+/i' => 'ruby',
        '/^\s*use\s+\w+|my\s+\$|sub\s+\w+\s*\{|package\s+\w+|require\s|print\s+"|<<\s*END/' => 'perl',
        '/^#!\s*\/bin\/(ba)?sh|^\s*export\s+\w+=|^\s*\$\{?\w+|^\s*function\s+\w+\s*\{/' => 'bash',
        '/^\s*@echo\s|^\s*echo\s|^\s*set\s+\w+|^\s*if\s+(not\s+)?exist|^\s*for\s+%%|^\s*goto\s/' => 'batch',
        '/^\s*function\s+\w+|^\s*param\s*\(|^\s*Write-\w+|^\s*\$\w+\s*=|^\s*Get-\w+|^\s*Set-\w+|^\s*Import-Module/' => 'powershell',
        '/^\s*local\s+\w+|^\s*function\s+\w+\s*\(|^\s*print\s*\(|--\s*\[|require\s*["\x27]?\w+|^\s*if\s+\w+\s+then/' => 'lua',
        '/^\s*library\s*\(|^\s*data\.frame|^\s*function\s*\([^)]*\)\s*\{|^\s*ggplot|^\s*plot\s*\(|^\s*summary\s*\(|^\s*lm\s*\(/' => 'r',
        '/^\s*package\s+main|^\s*import\s*\(|^\s*func\s+\w+\s*\(|^\s*fmt\.\w+|^\s*:=|^\s*chan\s/' => 'go',
        '/^\s*fn\s+\w+|^\s*fn\s+main|^\s*use\s+\w+|^\s*println!\s*\(|^\s*let\s+mut\s|^\s*impl\s+\w+|^\s*struct\s+\w+/i' => 'rust',
        '/^\s*using\s+System|^\s*namespace\s+\w+|^\s*(public|private|internal|protected)\s+(static\s+)?(class|struct|interface|enum)/' => 'csharp',
        '/^\s*public\s+class\s+\w+|^\s*import\s+java\.|^\s*public\s+static\s+void\s+main|^\s*System\.out\./' => 'java',
        '/^\s*fun\s+\w+|^\s*val\s+\w+|^\s*var\s+\w+\s*:|^\s*class\s+\w+.*\(|^\s*object\s+\w+/' => 'kotlin',
        '/^\s*fn\s+\w+|^\s*class\s+\w+|^\s*import\s+dart:|^\s*void\s+main\s*\(|^\s*lateinit\s+var/' => 'swift',
        '/^\s*void\s+main\s*\(|^\s*import\s+dart:|^\s*class\s+\w+\s+(extends|implements)|^\s*var\s+\w+\s*=|^\s*print\s*\(/' => 'dart',
        '/^\s*(val|var)\s+\w+\s*:|^\s*def\s+\w+|^\s*class\s+\w+.*\(|^\s*object\s+\w+|^\s*trait\s+\w+/i' => 'scala',
        '/^\s*module\s+\w+\s+where|^\s*import\s+System\.|^\s*main\s*=\s*do|^\s*\w+\s*::\s*\w+/' => 'haskell',
        '/^\s*let\s+\w+\s+=|^\s*module\s+\w+|^\s*open\s+\w+|^\s*type\s+\w+/i' => 'ocaml',
        '/^\s*-\w+\s+\w+\(|^\s*module\s+\w+|^\s*-spec\s+\w+|^\s*start\s*\(\)|^\s*receive\s+->/' => 'erlang',
        '/^\s*defmodule\s+\w+|^\s*def\s+\w+|^\s*use\s+GenServer|^\s*import\s+\w+/i' => 'elixir',
        '/^\s*\(ns\s|^\s*defn\s|^\s*defmacro\s|^\s*let\s*\[|^\s*require\s|^\s*do\s*$/' => 'clojure',
        '/^\s*class\s+\w+|^\s*def\s+\w+|^\s*import\s+\w+|^\s*println\s|^\s*defstatic\s/' => 'groovy',
        '/^\s*class\s+\w+|^\s*def\s+\w+|^\s*require\s|^\s*include\s|^\s*macro\s/' => 'crystal',
        '/^\s*(proc|let|const|var|func|template|import|module)\s/' => 'nim',
        '/^\s*(const|var|pub\s+fn|fn|test|comptime)\s/' => 'zig',
        '/^\s*(import|module|class|struct|void|int|char|auto)\s/' => 'd',
        '/^\s*(program|subroutine|function|module|contains|end\s+program)/i' => 'fortran',
        '/^\s*(SELECT|INSERT|UPDATE|DELETE|CREATE|ALTER|DROP|EXEC|DECLARE)\s/i' => 'sql',
        '/^\s*<!DOCTYPE\s|^\s*<html|^\s*<head|^\s*<body|^\s*<div|^\s*<span|^\s*<p\s/i' => 'html',
        '/^\s*[.#@]\w+|^\s*\{|^\s*color\s*:|^\s*margin\s*:|^\s*padding\s*:|^\s*font-size\s*:/i' => 'css',
        '/^\s*(section|global|_start|mov|push|pop|call|ret|xor|jmp|je|jne)\s/' => 'assembly',
    ];
    $supported = ['python','javascript','typescript','php','ruby','perl','lua','go','csharp','java','kotlin','scala','bash'];
    foreach ($signatures as $pattern => $lang) {
        if (preg_match($pattern, $code)) {
            return in_array($lang, $supported) ? $lang : 'generic';
        }
    }
    return 'generic';
}
