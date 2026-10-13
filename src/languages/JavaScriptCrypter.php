<?php
class JavaScriptCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'function_wrap':
                    $code = "Function(" . json_encode($code) . ")()"; break;
                case 'iife':
                    $code = "(function(){ " . $code . " })()"; break;
                case 'async_wrap':
                    $code = "(async()=>{ " . $code . " })()"; break;
                case 'proxy_obfuscate':
                    $code = self::proxyObfuscate($code); break;
            }
        }
        return $code;
    }

    public static function proxyObfuscate($code) {
        $b64 = base64_encode($code);
        return "new Proxy({},{ get:(_,__)=>eval(atob(" . json_encode($b64) . ")) }).x";
    }

    public static function jsaEncode($code) {
        $parts = [];
        for ($i = 0; $i < strlen($code); $i++) {
            $parts[] = 'String.fromCharCode(' . ord($code[$i]) . ')';
        }
        return implode('+', $parts);
    }

    public static function arrayMapObfuscate($code) {
        $b64 = base64_encode($code);
        $arr = implode(',', array_map(function($c) { return ord($c); }, str_split($code)));
        return "eval(String.fromCharCode.apply(null,[" . $arr . "]))";
    }
}
