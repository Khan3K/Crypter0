<?php
class RCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval_parse':
                    $code = "eval(parse(text=" . var_export($code, true) . "))"; break;
                case 'do_call':
                    $code = "do.call('eval', list(" . var_export($code, true) . "))"; break;
                case 'source_text':
                    $b64 = base64_encode($code);
                    $code = "eval(parse(text=rawToChar(base64decode(" . var_export($b64, true) . "))))"; break;
            }
        }
        return $code;
    }
}
