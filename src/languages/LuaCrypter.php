<?php
class LuaCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'load':
                    $code = "load(" . var_export($code, true) . ")()"; break;
                case 'loadstring':
                    $code = "loadstring(" . var_export($code, true) . ")()"; break;
                case 'base64_load':
                    $b64 = base64_encode($code);
                    $code = "load((loadstring('return base64 and base64.decode or (require(\"base64\")).decode'))()(" . var_export($b64, true) . "))()"; break;
            }
        }
        return $code;
    }
}
