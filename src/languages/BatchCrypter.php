<?php
class BatchCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'call':
                    $code = "call " . $code; break;
                case 'cmd_c':
                    $b64 = base64_encode($code);
                    $code = "cmd /c echo " . $b64 . " | certutil -decode - 2>nul | cmd"; break;
                case 'delayed_exp':
                    $code = self::delayedExpansion($code); break;
            }
        }
        return $code;
    }

    public static function delayedExpansion($code) {
        $b64 = base64_encode($code);
        return "setlocal enabledelayedexpansion\nset _=" . $b64 . "\nfor %i in (!_!) do @echo %i";
    }
}
