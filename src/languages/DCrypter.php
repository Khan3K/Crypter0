<?php
class DCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "import std.process : executeShell;\nimport std.base64 : decode;\nexecuteShell(cast(string)Base64.decode(\"{$b64}\"));";
                    break;
                case 'mixin':
                    $code = "mixin(" . var_export($code, true) . ");";
                    break;
            }
        }
        return $code;
    }
}
