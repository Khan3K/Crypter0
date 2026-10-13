<?php
class AntiTamper {
    public static function apply($code, $lang, $mode = 'tamper') {
        switch ($mode) {
            case 'tamper': return AntiDebug::inject($code, $lang, 'tamper');
            case 'integrity': return AntiDebug::inject($code, $lang, 'integrity');
            case 'checksum': return AntiDebug::inject($code, $lang, 'checksum');
            case 'callback': return AntiDebug::inject($code, $lang, 'callback');
            default: return $code;
        }
    }
}
