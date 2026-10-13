<?php
class SwiftCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "import Foundation\nlet _d = Data(base64Encoded: \"{$b64}\")!\nlet _s = String(data: _d!, encoding: .utf8)!\n_ = unsafeBitCast(NSSelectorFromString(\"_method:\"), to: (@convention(c)(AnyObject, Selector, String) -> Void).self)(NSString(\"eval\"), NSSelectorFromString(\"_eval:\"), _s)";
                    break;
                case 'unsafe':
                    $code = "withUnsafeMutablePointer(to: &" . $code . ") { _ = \$0.pointee }";
                    break;
            }
        }
        return $code;
    }
}
