<?php
class GenericCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'base64_wrap':
                    $b64 = base64_encode($code);
                    $code = "echo " . var_export($b64, true) . " | base64 -d";
                    break;
                case 'comment_strip':
                    $code = preg_replace('/\/\*[\s\S]*?\*\/|(?<!:)--.*$|#.*$/m', '', $code);
                    break;
                case 'whitespace_strip':
                    $code = preg_replace('/\s+/', ' ', $code);
                    $code = preg_replace('/\s*([{}();,=+\-*\/<>!&|])\s*/', '$1', $code);
                    break;
            }
        }
        return $code;
    }
}
