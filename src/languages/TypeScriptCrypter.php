<?php
class TypeScriptCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval': $code = "eval(" . json_encode($code) . ")"; break;
                case 'function': $code = "Function(" . json_encode($code) . ")()"; break;
                case 'iife': $code = "(function(){ " . $code . " })()"; break;
                case 'async_wrap': $code = "(async()=>{ " . $code . " })()"; break;
                case 'new_function': $code = "new Function(" . json_encode($code) . ")()"; break;
                case 'promise_wrap': $code = "new Promise((r)=>{ " . $code . "; r(); })"; break;
            }
        }
        return $code;
    }
    public static function stringEncode($code) {
        return preg_replace_callback('/"(?:[^"\\\\]|\\\\.)*"|\'(?:[^\'\\\\]|\\\\.)*\'/s', function($m) {
            $s = $m[0];
            $inner = substr($s, 1, -1);
            $enc = '';
            for ($i = 0; $i < strlen($inner); $i++) { $enc .= '\\x' . dechex(ord($inner[$i])); }
            return "'" . $enc . "'";
        }, $code);
    }
    public static function obfuscateLiterals($code) {
        $code = preg_replace_callback('/(\d+)/', function($m) {
            $n = intval($m[1]);
            if ($n < 2) return $m[0];
            $a = random_int(1, $n - 1);
            $b = $n - $a;
            $op = random_int(0, 3);
            switch ($op) {
                case 0: return "($a+$b)";
                case 1: return "($a-(-$b))";
                case 2: return "($a*1+$b)";
                default: return "($b+$a)";
            }
        }, $code);
        return $code;
    }
}
