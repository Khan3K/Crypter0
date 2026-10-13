<?php
class AntiAI {
    public static function process($code, $lang, $mode = 'basic', $options = []) {
        switch ($mode) {
            case 'basic': return self::basicAntiAI($code, $lang);
            case 'noise': case 'noise_inject': return self::noiseInject($code, $lang);
            case 'confuse': return self::confuseAI($code, $lang);
            case 'semantic_drift': return self::semanticDrift($code, $lang);
            case 'rename_entities': return self::renameEntities($code, $lang);
            case 'structure_scramble': return self::fullAntiAI($code, $lang);
            case 'full': return self::fullAntiAI($code, $lang);
            default: return $code;
        }
    }

    private static function basicAntiAI($code, $lang) {
        $lines = explode("\n", $code);
        $result = [];
        $deadLines = self::getDeadLines($lang, 5);
        foreach ($lines as $idx => $line) {
            $result[] = $line;
            if ($line !== '' && random_int(0, 4) === 0 && !empty($deadLines)) {
                $trimmed = rtrim($line);
                if (substr($trimmed, -1) === ':') continue;
                $indent = '';
                for ($j = 0; $j < strlen($line); $j++) {
                    if ($line[$j] === ' ' || $line[$j] === "\t") $indent .= $line[$j];
                    else break;
                }
                $result[] = $indent . $deadLines[array_rand($deadLines)];
            }
        }
        if (!empty($deadLines)) {
            $result[] = $deadLines[array_rand($deadLines)];
        }
        return implode("\n", $result);
    }

    private static function noiseInject($code, $lang) {
        $deadLines = self::getDeadLines($lang, 8);
        $insertCount = min(5, count($deadLines));
        $lines = explode("\n", $code);
        for ($i = 0; $i < $insertCount; $i++) {
            $pos = random_int(1, max(1, count($lines) - 1));
            $trimmed = rtrim($lines[$pos] ?? '');
            if (substr($trimmed, -1) === ':') { $pos = min($pos + 1, count($lines)); }
            $line = $deadLines[array_rand($deadLines)];
            $indent = '';
            $contextLine = $lines[$pos] ?? $lines[max(0, $pos - 1)];
            if ($contextLine !== null) {
                for ($j = 0; $j < strlen($contextLine); $j++) {
                    if ($contextLine[$j] === ' ' || $contextLine[$j] === "\t") $indent .= $contextLine[$j];
                    else break;
                }
            }
            array_splice($lines, $pos, 0, [$indent . $line]);
        }
        return implode("\n", $lines);
    }

    private static function confuseAI($code, $lang) {
        $prefix = self::getConfusePrefix($lang);
        return $prefix . "\n" . $code;
    }

    private static function semanticDrift($code, $lang) {
        $lines = explode("\n", $code);
        $result = [];
        foreach ($lines as $idx => $line) {
            $result[] = $line;
            if ($line !== '' && random_int(0, 5) === 0) {
                $trimmed = rtrim($line);
                if (substr($trimmed, -1) === ':') continue;
                $indent = '';
                for ($j = 0; $j < strlen($line); $j++) {
                    if ($line[$j] === ' ' || $line[$j] === "\t") $indent .= $line[$j];
                    else break;
                }
                $var = '_' . RandomProvider::string(8);
                $phrases = [
                    'is a placeholder',
                    'temporarily assigned',
                    'used during refactoring',
                    'reserved for future use',
                    'legacy support variable',
                    'was moved elsewhere',
                    'intermediate computation',
                    'debug artifact',
                ];
                $phrase = $phrases[array_rand($phrases)];
                $comment = self::langCommentInline($lang, $var . ' = ' . $phrase);
                $result[] = $indent . $comment;
            }
        }
        return implode("\n", $result);
    }

    private static function renameEntities($code, $lang) {
        $lines = explode("\n", $code);
        $result = [];
        $entityCount = 0;
        $maxRenames = min(10, (int)(count($lines) / 3));
        foreach ($lines as $idx => $line) {
            $trimmed = ltrim($line);
            $renamed = false;
            if (preg_match('/^#(.*)$/', $trimmed, $m)) {
                if ($entityCount < $maxRenames && random_int(0, 2) === 0) {
                    $renamed = true;
                    $entityCount++;
                    $fakeRef = '_' . RandomProvider::string(6);
                    $indent = substr($line, 0, strlen($line) - strlen($trimmed));
                    $newComment = substr($line, 0, strrpos($line, '#'));
                    $suffix = self::getEntitySuffix($lang);
                    $result[] = $indent . '#' . $m[1] . ' ' . $suffix;
                }
            } elseif (preg_match('/^\\/\\/(.*)$/', $trimmed, $m)) {
                if ($entityCount < $maxRenames && random_int(0, 2) === 0) {
                    $renamed = true;
                    $entityCount++;
                    $indent = substr($line, 0, strlen($line) - strlen($trimmed));
                    $suffix = self::getEntitySuffix($lang);
                    $result[] = $indent . '//' . $m[1] . ' ' . $suffix;
                }
            } elseif (preg_match('/\\/\\*.*\\*\\//', $trimmed)) {
                if ($entityCount < $maxRenames && random_int(0, 2) === 0) {
                    $renamed = true;
                    $entityCount++;
                    $suffix = self::getEntitySuffix($lang);
                    $result[] = preg_replace('/\\/\\/', '/* ', $line, 1) . ' ' . $suffix;
                }
            } elseif (preg_match('/^(.+?)\\s*=\\s*(["\'])(.*?)\\2/', $trimmed, $m)) {
                if ($entityCount < $maxRenames && random_int(0, 3) === 0) {
                    $renamed = true;
                    $entityCount++;
                    $fakeRef = '_' . RandomProvider::string(6);
                    $indent = substr($line, 0, strlen($line) - strlen($trimmed));
                    $result[] = $indent . $fakeRef . ' = ' . $m[2] . $m[3] . $m[2] . ';';
                }
            }
            if (!$renamed) {
                $result[] = $line;
            }
        }
        return implode("\n", $result);
    }

    private static function langCommentInline($lang, $text) {
        switch ($lang) {
            case 'python': case 'ruby': case 'perl': case 'bash':
                return '# ' . $text;
            case 'lua': case 'haskell': case 'go':
                return '// ' . $text;
            case 'javascript': case 'typescript': case 'php': case 'csharp':
            case 'java': case 'kotlin': case 'scala':
                return '// ' . $text;
            default:
                return '// ' . $text;
        }
    }

    private static function getEntitySuffix($lang) {
        $parts = [RandomProvider::string(4), RandomProvider::string(4)];
        switch ($lang) {
            case 'python': case 'ruby': case 'perl': case 'bash':
                return '(see _' . $parts[0] . ')';
            case 'javascript': case 'typescript': case 'php': case 'csharp':
            case 'java': case 'kotlin': case 'scala':
                return '{ @' . $parts[0] . ' }';
            case 'go': case 'lua':
                return '[ref:' . $parts[0] . ']';
            default:
                return '(' . $parts[0] . ')';
        }
    }

    private static function fullAntiAI($code, $lang) {
        $code = self::confuseAI($code, $lang);
        $code = self::noiseInject($code, $lang);
        $code = self::basicAntiAI($code, $lang);
        return $code;
    }

    private static function getDeadLines($lang, $count) {
        $lines = [];
        for ($i = 0; $i < $count; $i++) {
            $var = '_' . RandomProvider::string(8);
            $val = random_int(0, 9999);
            $val2 = random_int(0, 9999);
            switch ($lang) {
                case 'python':
                    $lines[] = "{$var} = {$val}";
                    $lines[] = "{$var} = lambda x: x + {$val}";
                    $lines[] = "if False: {$var} = {$val2}";
                    $lines[] = "{$var} = [{$val} for _ in range(0) if False]";
                    $lines[] = "type('{$var}', (), {'val': {$val}})";
                    break;
                case 'javascript': case 'typescript':
                    $lines[] = "var {$var} = {$val};";
                    $lines[] = "var {$var} = function(x){ return x + {$val}; };";
                    $lines[] = "if(false){ var {$var} = {$val2}; }";
                    $lines[] = "void {$var};";
                    break;
                case 'php':
                    $lines[] = "\${$var} = {$val};";
                    $lines[] = "if (false) { \${$var} = {$val2}; }";
                    break;
                case 'go':
                    $lines[] = "_ = {$val}";
                    $lines[] = "if false { _ = {$val2} }";
                    $lines[] = "_ = func() int { return {$val} }()";
                    break;
                case 'csharp':
                    $lines[] = "var {$var} = {$val};";
                    $lines[] = "if (false) { var {$var} = {$val2}; }";
                    $lines[] = "void _{$var}() { return; }";
                    break;
                case 'java':
                    $lines[] = "int {$var} = {$val};";
                    $lines[] = "if (false) { int {$var} = {$val2}; }";
                    $lines[] = "static void _{$var}() { return; }";
                    break;
                case 'kotlin':
                    $lines[] = "val {$var} = {$val}";
                    $lines[] = "if (false) { val {$var} = {$val2} }";
                    $lines[] = "fun _{$var}() { return }";
                    break;
                case 'scala':
                    $lines[] = "val {$var} = {$val}";
                    $lines[] = "if (false) { val {$var} = {$val2} }";
                    $lines[] = "def _{$var}() { return }";
                    break;
                default:
                    $lines[] = "// {$var} = {$val}";
                    $lines[] = "// {$var} = {$val2}";
            }
        }
        return $lines;
    }

    private static function getConfusePrefix($lang) {
        $a = RandomProvider::string(12);
        $b = RandomProvider::string(12);
        $c = RandomProvider::string(12);
        switch ($lang) {
            case 'python':
                return "# Module {$a}\n# Version: 1.0\n# Encoding: utf-8\n"
                     . "{$b} = lambda: None\n"
                     . "del {$b}";
            case 'javascript': case 'typescript':
                return "// Module {$a}\n// Version: 1.0\n"
                     . "var {$b} = typeof undefined;\n"
                     . "void {$b};";
            case 'php':
                return "<?php // Module {$a} ?>\n";
            case 'go':
                return "// Package {$a}\n// Version: 1.0\n"
                     . "var _{$b} = func() interface{} { return nil }()\n"
                     . "func init() { _ = _{$b} }";
            case 'csharp':
                return "// Module {$a}\n// Version: 1.0\n"
                     . "class _{$b} { static object _{$c} = null; }\n";
            case 'java':
                return "// Module {$a}\n// Version: 1.0\n"
                     . "class _{$b} { static Object _{$c} = null; }\n";
            case 'kotlin':
                return "// Module {$a}\n// Version: 1.0\n"
                     . "val _{$b}: Any? = null\n";
            case 'scala':
                return "// Module {$a}\n// Version: 1.0\n"
                     . "object _{$b} { val _{$c}: Any = null }\n";
            default:
                return "# Module {$a}";
        }
    }
}
