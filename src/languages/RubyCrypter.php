<?php
class RubyCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $code = "eval(" . var_export($code, true) . ")"; break;
                case 'instance_eval':
                    $code = "self.instance_eval(" . var_export($code, true) . ")"; break;
                case 'class_eval':
                    $code = "Class.new.instance_eval(" . var_export($code, true) . ")"; break;
                case 'send':
                    $code = self::sendWrap($code); break;
                case 'define_method':
                    $code = self::defineMethodWrap($code); break;
            }
        }
        return $code;
    }

    public static function sendWrap($code) {
        $b64 = base64_encode($code);
        return "send(:eval, Base64.decode64(" . var_export($b64, true) . "))";
    }

    public static function defineMethodWrap($code) {
        $b64 = base64_encode($code);
        return "define_method(:_x) { eval(Base64.decode64(" . var_export($b64, true) . ")) }; send(:_x)";
    }
}
