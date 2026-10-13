<?php
class RustCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'string_encode':
                    $code = self::stringEncode($code); break;
                case 'macro_obfuscate':
                    $code = self::macroObfuscate($code); break;
            }
        }
        return $code;
    }

    public static function stringEncode($code) {
        return preg_replace_callback('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/', function($m) {
            $chars = str_split($m[1]);
            $encoded = '';
            foreach ($chars as $c) {
                $encoded .= '\\x{' . dechex(ord($c)) . '}';
            }
            return '"' . $encoded . '"';
        }, $code);
    }

    public static function macroObfuscate($code) {
        $m = RandomProvider::string(8);
        $code = "macro_rules! _{$m} { (\$x:expr) => { \$x }; }\n" . $code;
        return $code;
    }
}
