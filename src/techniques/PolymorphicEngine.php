<?php
class PolymorphicEngine {
    public static function process($code, $lang, $mode = 'full', $options = []) {
        $modes = ['scramble_comments', 'add_whitespace', 'swap_quotes', 'full'];
        $targetModes = $mode === 'full' ? $modes : [$mode];
        $mutations = [];
        foreach ($targetModes as $m) {
            switch ($m) {
                case 'scramble_comments': $mutations[] = function($c) { return self::scrambleComments($c); }; break;
                case 'add_whitespace': $mutations[] = function($c) { return self::addRandomWhitespace($c); }; break;
                case 'swap_quotes': $mutations[] = function($c) { return self::swapStringQuotes($c); }; break;
            }
        }
        if (empty($mutations)) {
            $mutations = [
                function($c) { return self::scrambleComments($c); },
                function($c) { return self::addRandomWhitespace($c); },
                function($c) { return self::reorderExpressions($c); },
                function($c) { return self::swapStringQuotes($c); },
            ];
        }
        shuffle($mutations);
        foreach ($mutations as $mutation) {
            $code = $mutation($code);
        }
        return $code;
    }

    private static function scrambleComments($code) {
        $lines = explode("\n", $code);
        foreach ($lines as &$line) {
            $randStr = RandomProvider::string(random_int(5, 15));
            if (preg_match('/#.*$/', $line)) {
                if (random_int(0, 3) === 0) {
                    $line = preg_replace('/#.*/', '# ' . $randStr, $line, 1);
                }
            } elseif (preg_match('/\\/\\/(?!\\/).*$/', $line)) {
                if (random_int(0, 3) === 0) {
                    $styles = ['/* ' . $randStr . ' */', '// ' . $randStr];
                    $line = preg_replace('/\\/\\/.*/', $styles[array_rand($styles)], $line, 1);
                }
            } elseif (preg_match('/\\/\\*.*?\\*\\//s', $line)) {
                if (random_int(0, 3) === 0) {
                    $line = preg_replace('/\\/\\*.*?\\*\\//s', '/* ' . $randStr . ' */', $line, 1);
                }
            } elseif (preg_match('/--.*$/', $line)) {
                if (random_int(0, 3) === 0) {
                    $line = preg_replace('/--.*/', '-- ' . $randStr, $line, 1);
                }
            } elseif (preg_match('/;;.*$/', $line)) {
                if (random_int(0, 3) === 0) {
                    $line = preg_replace('/;;.*/', ';; ' . $randStr, $line, 1);
                }
            } elseif (preg_match('/<!--.*-->/', $line)) {
                if (random_int(0, 3) === 0) {
                    $line = preg_replace('/<!--.*-->/', '<!-- ' . $randStr . ' -->', $line, 1);
                }
            }
        }
        return implode("\n", $lines);
    }

    private static function addRandomWhitespace($code) {
        $lines = explode("\n", $code);
        $result = [];
        foreach ($lines as $i => $line) {
            if (strlen(trim($line)) > 0 && random_int(0, 6) === 0) {
                $result[] = '';
            }
            $result[] = $line;
        }
        return implode("\n", $result);
    }

    private static function reorderExpressions($code) {
        $lines = explode("\n", $code);
        if (count($lines) < 2) return $code;
        $reorderable = [];
        for ($i = 0; $i < count($lines); $i++) {
            $trimmed = ltrim($lines[$i]);
            if ($trimmed === '' || preg_match('/^\s*(def |class |if |for |while |try|except|finally|else|elif|with |return |import |from |function |var |let |const |package |import\s)/', $trimmed)) {
                $reorderable[] = -1;
            } else {
                $reorderable[] = $i;
            }
        }
        $swappable = array_values(array_filter($reorderable, function($v) { return $v !== -1; }));
        if (count($swappable) < 2) return $code;
        $rounds = random_int(1, min(3, (int)(count($swappable) / 2)));
        for ($r = 0; $r < $rounds; $r++) {
            $a = $swappable[array_rand($swappable)];
            $b = $swappable[array_rand($swappable)];
            if ($a !== $b) {
                $indentA = strlen($lines[$a]) - strlen(ltrim($lines[$a]));
                $indentB = strlen($lines[$b]) - strlen(ltrim($lines[$b]));
                $temp = $lines[$a];
                $lines[$a] = str_repeat(' ', $indentB) . ltrim($temp);
                $lines[$b] = str_repeat(' ', $indentA) . ltrim($lines[$b]);
            }
        }
        return implode("\n", $lines);
    }

    private static function swapStringQuotes($code) {
        $lines = explode("\n", $code);
        foreach ($lines as &$line) {
            if (random_int(0, 3) === 0) {
                $line = preg_replace_callback('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/', function($m) {
                    return "'" . str_replace("'", "\\'", $m[1]) . "'";
                }, $line);
            }
        }
        return implode("\n", $lines);
    }
}
