<?php
class BashCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $escaped = addslashes($code);
                    $code = "eval \"" . $escaped . "\""; break;
                case 'base64':
                    $b64 = base64_encode($code);
                    $code = "eval \$(echo " . $b64 . " | base64 -d)"; break;
                case 'var_indirect':
                    $code = self::varIndirect($code); break;
                case 'substitution':
                    $code = self::substitutionChain($code); break;
            }
        }
        return $code;
    }

    public static function varIndirect($code) {
        $b64 = base64_encode($code);
        $v = RandomProvider::string(5);
        return "{$v}=" . $b64 . ";eval \$(base64 -d <<< \${!{$v}} )";
    }

    public static function substitutionChain($code) {
        $b64 = base64_encode($code);
        return "eval \$(echo \$(echo " . $b64 . " | base64 -d))";
    }
}
