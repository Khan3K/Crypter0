<?php
class JavaCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "import javax.script.*;\nvar _e = new ScriptEngineManager().getEngineByName(\"nashorn\");\n_e.eval(new String(java.util.Base64.getDecoder().decode(\"{$b64}\")));";
                    break;
                case 'runtime':
                    $b64 = base64_encode($code);
                    $code = "Runtime.getRuntime().exec(new String[]{\"java\",\"-e\",new String(java.util.Base64.getDecoder().decode(\"{$b64}\"))});";
                    break;
                case 'classloader':
                    $b64 = base64_encode($code);
                    $code = "new ClassLoader(){{public Class<?> findClass(String n){{return defineClass(n,java.util.Base64.getDecoder().decode(\"{$b64}\"),0,base64Decode.length);}}}};";
                    break;
                case 'reflection':
                    $code = "Class.forName(\"_D\").getDeclaredMethod(\"_m\").invoke(null);";
                    break;
            }
        }
        return $code;
    }
    public static function stringEncode($code) {
        return preg_replace_callback('/"(?:[^"\\\\]|\\\\.)*"/s', function($m) {
            $inner = substr($m[0], 1, -1);
            $parts = [];
            for ($i = 0; $i < strlen($inner); $i++) $parts[] = '(char)' . ord($inner[$i]);
            return 'new String(new char[]{'.implode(',',$parts).'})';
        }, $code);
    }
}
