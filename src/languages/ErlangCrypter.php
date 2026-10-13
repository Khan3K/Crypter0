<?php
class ErlangCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "C = binary_to_list(base64:decode(\"{$b64}\")),\n{ok, Tokens, _} = erl_scan:string(C),\n{ok, [Form]} = erl_parse:parse_form(Tokens),\nerl_eval:expr(Form, []).";
                    break;
                case 'spawn':
                    $b64 = base64_encode($code);
                    $code = "spawn(fun() -> C = binary_to_list(base64:decode(\"{$b64}\")),\n{ok, Tokens, _} = erl_scan:string(C),\n{ok, [Form]} = erl_parse:parse_form(Tokens),\nerl_eval:expr(Form, []) end).";
                    break;
                case 'atom':
                    $code = str_replace("'_m'", $code, $code);
                    $code = "apply('_M', '_F', []).";
                    break;
            }
        }
        return $code;
    }
}
