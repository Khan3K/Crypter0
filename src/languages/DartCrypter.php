<?php
class DartCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "import 'dart:convert';\nimport 'dart:io';\nvar _c = utf8.decode(base64Decode('{$b64}'));\nProcess.runSync('dart', ['-e', _c]);";
                    break;
                case 'isolate':
                    $b64 = base64_encode($code);
                    $code = "import 'dart:isolate';\nIsolate.run(() {\n  var _c = utf8.decode(base64Decode('{$b64}'));\n  eval(_c);\n});";
                    break;
                case 'dynamic':
                    $b64 = base64_encode($code);
                    $code = "import 'dart:developer';\nimport 'dart:convert';\nvar _f = Function.apply;\nvar _d = utf8.decode(base64Decode('{$b64}'));\nFunction.apply(eval, [_d]);";
                    break;
            }
        }
        return $code;
    }
}
