<?php
class InstructionSubstitution {
    public static function process($code, $lang, $mode = 'basic', $options = []) {
        switch ($mode) {
            case 'basic': return self::basicSubstitute($code, $lang);
            case 'arithmetic': return self::arithmeticSubstitute($code, $lang);
            case 'string_split': return self::stringSplitSubstitute($code, $lang);
            case 'full': return self::fullSubstitute($code, $lang);
            default: return $code;
        }
    }

    private static function basicSubstitute($code, $lang) {
        if ($lang === 'python') {
            $code = preg_replace_callback('/(\d+)\s*\+\s*(\d+)/', function($m) { return $m[1] + $m[2]; }, $code);
            $code = str_replace('True', 'not not 1', $code);
            $code = str_replace('False', 'not 1', $code);
            $code = str_replace('None', 'type(None)()', $code);
        }
        if ($lang === 'javascript' || $lang === 'typescript') {
            $code = str_replace('true', '!0', $code);
            $code = str_replace('false', '!1', $code);
            $code = str_replace('undefined', 'void 0', $code);
            $code = str_replace('null', 'void 0', $code);
        }
        if ($lang === 'go') {
            $code = str_replace('true', 'bool(true)', $code);
            $code = str_replace('false', 'bool(false)', $code);
            $code = str_replace('nil', '(*int)(nil)', $code);
        }
        if ($lang === 'csharp') {
            $code = str_replace('true', 'Convert.ToBoolean(1)', $code);
            $code = str_replace('false', 'Convert.ToBoolean(0)', $code);
            $code = str_replace('null', 'default(object)', $code);
        }
        if ($lang === 'java') {
            $code = str_replace('true', 'Boolean.valueOf("true")', $code);
            $code = str_replace('false', 'Boolean.valueOf("false")', $code);
            $code = str_replace('null', '(Object)null', $code);
        }
        if ($lang === 'kotlin') {
            $code = str_replace('true', 'true.also { }', $code);
            $code = str_replace('false', 'false.also { }', $code);
            $code = str_replace('null', 'null.also { }', $code);
        }
        if ($lang === 'scala') {
            $code = str_replace('true', 'true.asInstanceOf[Boolean]', $code);
            $code = str_replace('false', 'false.asInstanceOf[Boolean]', $code);
            $code = str_replace('null', 'null.asInstanceOf[AnyRef]', $code);
        }
        if ($lang === 'ruby') {
            $code = str_replace('true', 'true.dup', $code);
            $code = str_replace('false', 'false.dup', $code);
            $code = str_replace('nil', 'nil.dup', $code);
        }
        if ($lang === 'perl') {
            $code = preg_replace_callback('/\b(\d+)\b/', function($m) {
                $n = intval($m[1]);
                $formats = [];
                if ($n > 0) $formats[] = sprintf('0x%x', $n);
                if ($n > 1) $formats[] = sprintf('0%o', $n);
                $formats[] = sprintf('0b%s', decbin($n));
                return $formats[array_rand($formats)];
            }, $code);
            $code = str_replace('true', '1==1', $code);
            $code = str_replace('false', '1==0', $code);
            $code = str_replace('undef', 'do{my $u}', $code);
        }
        return $code;
    }

    private static function arithmeticSubstitute($code, $lang) {
        $replacements = [
            '/(\b\d+)\b/' => function($m) {
                $n = intval($m[1]);
                if ($n > 10 && random_int(0, 2) === 0) {
                    $a = random_int(1, $n - 1);
                    return "($a+" . ($n - $a) . ")";
                }
                return $m[0];
            },
        ];
        foreach ($replacements as $pattern => $callback) {
            $code = preg_replace_callback($pattern, $callback, $code);
        }
        return $code;
    }

    private static function stringSplitSubstitute($code, $lang) {
        preg_match_all('/"([^"]+)"/', $code, $matches);
        foreach ($matches[1] as $str) {
            if (strlen($str) > 2) {
                $mid = (int)(strlen($str) / 2);
                $parts = [substr($str, 0, $mid), substr($str, $mid)];
                if ($lang === 'python') $replacement = "'" . $parts[0] . "'+'" . $parts[1] . "'";
                elseif ($lang === 'javascript') $replacement = "'" . $parts[0] . "'+'" . $parts[1] . "'";
                else $replacement = "'" . $parts[0] . "'+'" . $parts[1] . "'";
                $code = str_replace('"' . $str . '"', $replacement, $code);
            }
        }
        return $code;
    }

    private static function fullSubstitute($code, $lang) {
        $code = self::basicSubstitute($code, $lang);
        $code = self::arithmeticSubstitute($code, $lang);
        $code = self::stringSplitSubstitute($code, $lang);
        return $code;
    }
}
