<?php
class PerlCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $code = "eval(" . var_export($code, true) . ");"; break;
                case 'string_eval':
                    $b64 = base64_encode($code);
                    $code = "eval(decode_base64(" . var_export($b64, true) . "));"; break;
                case 'sort_trick':
                    $code = self::sortTrick($code); break;
            }
        }
        return $code;
    }

    public static function sortTrick($code) {
        $b64 = base64_encode($code);
        return "sub { eval(decode_base64(shift)) }->(" . var_export($b64, true) . ");";
    }
}
