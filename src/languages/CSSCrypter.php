<?php
class CSSCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'obfuscate':
                    $code = self::obfuscateProperties($code);
                    break;
                case 'minify':
                    $code = preg_replace('/\s+/', ' ', $code);
                    $code = preg_replace('/;\s*}/', '}', $code);
                    $code = preg_replace('/\s*{\s*/', '{', $code);
                    $code = preg_replace('/\s*}\s*/', '}', $code);
                    break;
                case 'encrypt_selectors':
                    $code = self::encryptSelectors($code);
                    break;
            }
        }
        return $code;
    }
    private static function obfuscateProperties($code) {
        $map = ['color' => '_c', 'background' => '_b', 'margin' => '_m', 'padding' => '_p', 'border' => '_br', 'display' => '_d', 'position' => '_pos', 'font-size' => '_fs', 'font-weight' => '_fw', 'text-align' => '_ta'];
        foreach ($map as $full => $short) { $code = str_replace($full, $short, $code); }
        return $code;
    }
    private static function encryptSelectors($code) {
        return preg_replace_callback('/([.#])([a-zA-Z_-][\w-]*)/s', function($m) {
            $name = $m[2];
            $enc = '';
            for ($i = 0; $i < strlen($name); $i++) $enc .= '&#' . ord($name[$i]) . ';';
            return $m[1] . $enc;
        }, $code);
    }
}
