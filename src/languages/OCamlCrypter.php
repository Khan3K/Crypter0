<?php
class OCamlCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "let c = Base64.decode_exn \"{$b64}\" in\nlet _ = Sys.command c in ()";
                    break;
                case 'dynlink':
                    $code = "Dynlink.loadfile \"_module.cmo\"";
                    break;
            }
        }
        return $code;
    }
}
