<?php
class ControlFlowObfuscator {
    public static function process($code, $lang, $mode = 'basic', $options = []) {
        switch ($mode) {
            case 'basic': return self::basicSplit($code, $lang, $options);
            case 'full': return self::fullObfuscate($code, $lang, $options);
            case 'flatten': return self::flatten($code, $lang);
            default: return self::basicSplit($code, $lang, $options);
        }
    }

    private static function basicSplit($code, $lang, $options = []) {
        $lines = explode("\n", $code);
        $minLines = $options['block_size'] ?? 4;
        if (count($lines) < $minLines) return $code;

        switch ($lang) {
            case 'python':
                $blocks = self::splitPythonBlocks($lines);
                if (count($blocks) < 2) return $code;
                $shuffled = $blocks;
                for ($i = count($shuffled) - 1; $i > 0; $i--) {
                    $j = random_int(0, $i);
                    [$shuffled[$i], $shuffled[$j]] = [$shuffled[$j], $shuffled[$i]];
                }
                $order = array_map('strval', array_keys($blocks));
                $shuffledOrder = array_map('strval', array_keys($shuffled));
                if ($order === $shuffledOrder) return $code;

                $varMap = [];
                foreach ($shuffledOrder as $idx => $newIdx) {
                    $varMap[$newIdx] = '_cf_' . RandomProvider::string(6);
                }
                $result = [];
                foreach ($shuffled as $idx => $block) {
                    $varName = $varMap[$idx];
                    $result[] = $varName . ' = lambda: (';
                    foreach ($block as $line) $result[] = '    ' . $line;
                    $result[] = ')';
                }
                foreach ($order as $idx) {
                    $result[] = $varMap[$idx] . '()';
                }
                return implode("\n", $result);

            case 'javascript': case 'typescript':
                $blocks = [];
                for ($i = 0; $i < count($lines); $i += max(1, (int)(count($lines) / 3))) {
                    $chunk = array_slice($lines, $i, random_int(1, 3));
                    $blocks[] = $chunk;
                }
                if (count($blocks) < 2) return $code;
                $order = array_keys($blocks);
                shuffle($order);
                $result = [];
                $fns = [];
                foreach ($order as $idx) {
                    $fnName = '_cf_' . RandomProvider::string(6);
                    $fns[] = $fnName;
                    $result[] = "var {$fnName}=function(){";
                    foreach ($blocks[$idx] as $line) $result[] = $line;
                    $result[] = "};";
                }
                foreach ($fns as $fn) $result[] = "{$fn}();";
                return implode("\n", $result);

            case 'go':
                $blocks = self::splitBraceBlocks($lines, $minLines);
                if (count($blocks) < 2) return $code;
                $order = array_keys($blocks);
                shuffle($order);
                $result = [];
                $fns = [];
                foreach ($order as $idx) {
                    $fnName = '_cf_' . RandomProvider::string(6);
                    $fns[] = $fnName;
                    $result[] = "func {$fnName}() {";
                    foreach ($blocks[$idx] as $line) $result[] = "    " . $line;
                    $result[] = "}";
                }
                foreach ($fns as $fn) $result[] = "{$fn}()";
                return implode("\n", $result);

            case 'csharp': case 'java': case 'kotlin':
                $blocks = self::splitBraceBlocks($lines, $minLines);
                if (count($blocks) < 2) return $code;
                $order = array_keys($blocks);
                shuffle($order);
                $result = [];
                $fns = [];
                $fnType = $lang === 'java' ? 'Runnable' : 'Action';
                foreach ($order as $idx) {
                    $fnName = '_cf_' . RandomProvider::string(6);
                    $fns[] = $fnName;
                    if ($lang === 'kotlin') {
                        $result[] = "val {$fnName} = {";
                    } else {
                        $result[] = " {$fnType} {$fnName} = () => {";
                    }
                    foreach ($blocks[$idx] as $line) $result[] = "        " . $line;
                    $result[] = "    }";
                }
                foreach ($fns as $fn) {
                    if ($lang === 'kotlin') $result[] = "{$fn}()";
                    elseif ($lang === 'java') $result[] = "{$fn}.run();";
                    else $result[] = "{$fn}();";
                }
                return implode("\n", $result);

            case 'scala':
                $blocks = self::splitBraceBlocks($lines, $minLines);
                if (count($blocks) < 2) return $code;
                $order = array_keys($blocks);
                shuffle($order);
                $result = [];
                $fns = [];
                foreach ($order as $idx) {
                    $fnName = '_cf_' . RandomProvider::string(6);
                    $fns[] = $fnName;
                    $result[] = "lazy val {$fnName} = {";
                    foreach ($blocks[$idx] as $line) $result[] = "    " . $line;
                    $result[] = "}";
                }
                foreach ($fns as $fn) $result[] = $fn;
                return implode("\n", $result);

            default:
                return $code;
        }
    }

    private static function splitPythonBlocks($lines) {
        $blocks = [];
        $current = [];
        $inDef = false;
        foreach ($lines as $line) {
            $trimmed = ltrim($line);
            if (preg_match('/^def\s+/', $trimmed) || preg_match('/^class\s+/', $trimmed)) {
                if (!empty($current)) $blocks[] = $current;
                $current = [$line];
                $inDef = true;
            } elseif ($inDef && ($trimmed === '' || preg_match('/^\s/', $line))) {
                $current[] = $line;
            } else {
                if (!empty($current)) $blocks[] = $current;
                $current = [$line];
                $inDef = false;
            }
        }
        if (!empty($current)) $blocks[] = $current;
        return $blocks;
    }

    private static function splitBraceBlocks($lines, $minLines = 4) {
        $blocks = [];
        $current = [];
        $braceDepth = 0;
        $inBlock = false;

        foreach ($lines as $line) {
            $trimmed = ltrim($line);
            if ($trimmed === '' && $braceDepth === 0 && count($current) >= $minLines) {
                $blocks[] = $current;
                $current = [];
                $inBlock = false;
                continue;
            }
            $opens = substr_count($line, '{');
            $closes = substr_count($line, '}');
            $braceDepth += $opens - $closes;
            $current[] = $line;
            if ($braceDepth > 0) $inBlock = true;
            if ($braceDepth <= 0 && $inBlock && count($current) >= $minLines) {
                $blocks[] = $current;
                $current = [];
                $inBlock = false;
            }
        }
        if (!empty($current)) $blocks[] = $current;
        return $blocks;
    }

    private static function fullObfuscate($code, $lang, $options = []) {
        $code = self::basicSplit($code, $lang);
        $lines = explode("\n", $code);
        $result = [];
        foreach ($lines as $line) {
            $result[] = $line;
            if ($line !== '' && random_int(0, 3) === 0) {
                $trimmed = rtrim($line);
                if (substr($trimmed, -1) === ':') continue;
                $indent = '';
                for ($j = 0; $j < strlen($line); $j++) {
                    if ($line[$j] === ' ' || $line[$j] === "\t") $indent .= $line[$j];
                    else break;
                }
                $result[] = $indent . self::langComment($lang, RandomProvider::string(16));
            }
        }
        return implode("\n", $result);
    }

    private static function langComment($lang, $text) {
        switch ($lang) {
            case 'python': case 'ruby': case 'perl': case 'bash':
            case 'elixir': case 'erlang': case 'powershell':
                return "# " . $text;
            case 'lua': case 'haskell': return "-- " . $text;
            case 'clojure': return ";; " . $text;
            case 'css': return "/* " . $text . " */";
            default: return "// " . $text;
        }
    }

    private static function flatten($code, $lang) {
        $lines = explode("\n", $code);
        if (count($lines) < 4) return $code;

        switch ($lang) {
            case 'python':
                $stmts = [];
                foreach ($lines as $line) {
                    $trimmed = ltrim($line);
                    if ($trimmed === '' || preg_match('/^\s/', $line) && !empty($stmts)) {
                        if (!empty($stmts)) $stmts[count($stmts) - 1] .= "\n" . $line;
                    } else {
                        $stmts[] = $line;
                    }
                }
                if (count($stmts) < 2) return $code;
                $order = range(0, count($stmts) - 1);
                shuffle($order);
                $stateVar = '_state_' . RandomProvider::string(6);
                $result = [$stateVar . ' = ' . $order[0]];
                $result[] = '_states = {';
                foreach ($order as $i) {
                    $result[] = "    $i: lambda: (" . trim($stmts[$i]) . "),";
                }
                $result[] = '}';
                $result[] = 'while ' . $stateVar . ' in _states:';
                $result[] = '    _states[' . $stateVar . ']()';
                foreach ($order as $i) {
                    $pos = array_search($i, $order);
                    $next = $order[$pos + 1] ?? -1;
                    $result[] = "    if $stateVar == $i: $stateVar = $next; continue";
                }
                $result[] = '    break';
                return implode("\n", $result);

            case 'javascript': case 'typescript':
                $stmts = [];
                foreach ($lines as $line) {
                    $trimmed = ltrim($line);
                    if ($trimmed === '') {
                        if (!empty($stmts)) $stmts[count($stmts) - 1] .= "\n" . $line;
                    } else {
                        $stmts[] = $line;
                    }
                }
                if (count($stmts) < 2) return $code;
                $order = range(0, count($stmts) - 1);
                shuffle($order);
                $stateVar = '_s' . RandomProvider::string(6);
                $result = [];
                $result[] = "(function(){";
                $result[] = "var $stateVar=" . $order[0] . ";";
                $result[] = "var _f={};";
                foreach ($order as $i) {
                    $escapedStmt = str_replace(["\\", "'"], ["\\\\", "\\'"], $stmts[$i]);
                    $result[] = "_f[$i]=function(){/*" . RandomProvider::string(8) . "*/$escapedStmt};";
                }
                $result[] = "while($stateVar in _f){";
                $result[] = "  _f[$stateVar]();";
                foreach ($order as $i) {
                    $pos = array_search($i, $order);
                    $next = $order[$pos + 1] ?? -1;
                    $result[] = '  if(' . $stateVar . '===' . $i . '){' . $stateVar . '=' . $next . ';continue;}';
                }
                $result[] = "  break;";
                $result[] = "}";
                $result[] = "})();";
                return implode("\n", $result);

            default:
                return self::basicSplit($code, $lang);
        }
    }
}
