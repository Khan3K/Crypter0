<?php
class NimCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "import osproc, base64\nlet c = decode(\"{$b64}\")\nexecCmd(c)";
                    break;
                case 'compile':
                    $code = "static:\n  execCmd(\"nim c -r <<< \\\"\" & \"{$code}\" & \"\\\"\")";
                    break;
            }
        }
        return $code;
    }
}
