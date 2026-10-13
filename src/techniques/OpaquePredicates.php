<?php
class OpaquePredicates {
    public static function process($code, $lang, $mode = 'basic', $options = []) {
        $density = $options['density'] ?? $mode;
        $predicates = [
            'basic' => 3, 'medium' => 8, 'heavy' => 15, 'extreme' => 30,
        ];
        $count = $predicates[$density] ?? 5;
        for ($i = 0; $i < $count; $i++) {
            $pred = self::generatePredicate($lang, $i);
            $code = self::insertPredicate($code, $pred, $lang);
        }
        return $code;
    }

    private static function generatePredicate($lang, $seed) {
        $type = $seed % 8;
        $c1 = random_int(2, 100);
        $c2 = random_int(2, 100);
        $n = random_int(5, 50);

        switch ($type) {
            case 0: return "($c1 * $c1) % $c2 == ($c1 % $c2) * ($c1 % $c2) % $c2";
            case 1: return "$c1 * $c2 == $c2 * $c1";
            case 2: return "($c1 + $c2) * ($c1 - $c2) == $c1 * $c1 - $c2 * $c2";
            case 3: return "$c1 * ($n + 1) - $c1 * $n == $c1";
            case 4: return ($n % 2 == 0) ? "($n * ($n + 1)) % 2 == 0" : "1 == 1";
            case 5: return "$c1 * $c2 - $c2 * $c1 == 0";
            case 6: return "($c1 ^ $c1) == 0";
            case 7: return "abs($c1 - $c2) >= 0";
            default: return "1 + 1 == 2";
        }
    }

    private static function insertPredicate($code, $pred, $lang) {
        $lines = explode("\n", $code);
        if (count($lines) < 2) return $code;
        $insertAt = random_int(1, max(1, count($lines) - 1));
        $indent = '';
        for ($i = 0; $i < strlen($lines[$insertAt]); $i++) {
            if ($lines[$insertAt][$i] === ' ' || $lines[$insertAt][$i] === "\t") $indent .= $lines[$insertAt][$i];
            else break;
        }

        switch ($lang) {
            case 'python':
                $comment = "# " . RandomProvider::string(12);
                $lines[$insertAt] = $indent . "if ($pred): None\n" . $indent . $comment . "\n" . $lines[$insertAt];
                break;
            case 'javascript': case 'typescript':
                $comment = "/* " . RandomProvider::string(12) . " */";
                $lines[$insertAt] = $indent . "if($pred){}\n" . $indent . $comment . "\n" . $lines[$insertAt];
                break;
            case 'php':
                $comment = "// " . RandomProvider::string(12);
                $lines[$insertAt] = $indent . "if($pred){}\n" . $indent . $comment . "\n" . $lines[$insertAt];
                break;
            case 'ruby': case 'perl': case 'bash': case 'elixir': case 'erlang':
                $comment = "# " . RandomProvider::string(12);
                $lines[$insertAt] = $indent . "if $pred; end\n" . $indent . $comment . "\n" . $lines[$insertAt];
                break;
            case 'lua':
                $comment = "-- " . RandomProvider::string(12);
                $lines[$insertAt] = $indent . "if $pred then end\n" . $indent . $comment . "\n" . $lines[$insertAt];
                break;
            case 'go': case 'rust': case 'csharp': case 'java': case 'kotlin': case 'swift':
            case 'dart': case 'scala': case 'groovy':
                $comment = "// " . RandomProvider::string(12);
                $lines[$insertAt] = $indent . "if ($pred) {}\n" . $indent . $comment . "\n" . $lines[$insertAt];
                break;
            default:
                $comment = "// " . RandomProvider::string(12);
                $lines[$insertAt] = $indent . "if($pred){}\n" . $indent . $comment . "\n" . $lines[$insertAt];
        }
        return implode("\n", $lines);
    }
}
