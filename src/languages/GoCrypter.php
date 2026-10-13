<?php
class GoCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'string_encode':
                    $code = self::stringEncode($code); break;
                case 'dead_vars':
                    $code = self::addDeadVars($code); break;
            }
        }
        return $code;
    }

    public static function stringEncode($code) {
        return preg_replace_callback('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/', function($m) {
            $chars = str_split($m[1]);
            $encoded = '';
            foreach ($chars as $c) {
                $encoded .= '\\x' . dechex(ord($c));
            }
            return '"' . $encoded . '"';
        }, $code);
    }

    public static function addDeadVars($code) {
        $v = RandomProvider::string(8);
        return $code . "\nvar _" . $v . " string = \"" . RandomProvider::string(20) . "\"\n";
    }
}
