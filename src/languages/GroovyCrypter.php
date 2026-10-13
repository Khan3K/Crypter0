<?php
class GroovyCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "def _c = new String(java.util.Base64.decoder.decode(\"{$b64}\"))\nnew GroovyShell().evaluate(_c)";
                    break;
                case 'shell':
                    $b64 = base64_encode($code);
                    $code = "Runtime.runtime.exec(['groovy','-e',new String(java.util.Base64.decoder.decode(\"{$b64}\"))].toArray(new String[0]))";
                    break;
                case 'classloader':
                    $b64 = base64_encode($code);
                    $code = "new GroovyClassLoader().parseClass(new String(java.util.Base64.decoder.decode(\"{$b64}\"))).newInstance()";
                    break;
            }
        }
        return $code;
    }
}
