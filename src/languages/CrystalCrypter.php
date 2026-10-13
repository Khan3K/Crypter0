<?php
class CrystalCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "require \"base64\"\n{{system(\"echo #{Base64.decode_string(\\\"{$b64}\\\")} | crystal eval\")}}";
                    break;
                case 'macro':
                    $code = "macro _e()\n  " . $code . "\nend\n_e()";
                    break;
            }
        }
        return $code;
    }
}
