<?php
class ClojureCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "(eval (read-string (String. (java.util.Base64/getDecoder().decode \"{$b64}\") \"UTF-8\")))";
                    break;
                case 'load':
                    $b64 = base64_encode($code);
                    $code = "(binding [*read-eval* false] (load-string (String. (java.util.Base64/getDecoder().decode \"{$b64}\") \"UTF-8\")))";
                    break;
            }
        }
        return $code;
    }
}
