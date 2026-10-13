<?php
class PHPCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $code = "eval(" . var_export($code, true) . ");"; break;
                case 'assert':
                    $code = "assert(" . var_export($code, true) . ");"; break;
                case 'base64_eval':
                    $b64 = base64_encode($code);
                    $code = "eval(base64_decode(" . var_export($b64, true) . "));"; break;
                case 'gzinflate':
                    $gz = base64_encode(gzencode($code));
                    $code = "eval(gzinflate(base64_decode(" . var_export($gz, true) . ")));"; break;
                case 'variable_vars':
                    $code = self::variableVarWrap($code); break;
                case 'create_function':
                    $code = self::createFuncWrap($code); break;
            }
        }
        return $code;
    }

    public static function variableVarWrap($code) {
        $b64 = base64_encode($code);
        $v1 = RandomProvider::string(5);
        $v2 = RandomProvider::string(5);
        return "\${$v1}='base64_decode';\${$v2}=\${$v1}(" . var_export($b64, true) . ");eval(\${$v2});";
    }

    public static function createFuncWrap($code) {
        $b64 = base64_encode($code);
        return "\$_=create_function('',base64_decode(" . var_export($b64, true) . "));\$_();";
    }

    public static function multiLayerEval($code) {
        $layers = random_int(2, 5);
        $current = var_export($code, true);
        for ($i = 0; $i < $layers; $i++) {
            $b64 = base64_encode($current);
            $current = "eval(base64_decode(" . var_export($b64, true) . "))";
        }
        return $current . ";";
    }
}
