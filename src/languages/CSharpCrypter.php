<?php
class CSharpCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "using System;\nusing System.Text;\nvar _c = Encoding.UTF8.GetString(Convert.FromBase64String(\"{$b64}\"));\nusing var _s = new Microsoft.CSharp.CSharpCodeProvider();\nvar _p = _s.CompileAssemblyFromSource(new System.CodeDom.Compiler.CompilerParameters(), _c);\n_p.CompiledAssembly.GetType(\"_D\").GetMethod(\"_M\").Invoke(null, null);";
                    break;
                case 'reflection':
                    $b64 = base64_encode($code);
                    $code = "System.Reflection.Assembly.Load(Convert.FromBase64String(\"{$b64}\"));";
                    break;
                case 'delegate':
                    $code = "((Action)(() => { " . $code . " }))();";
                    break;
            }
        }
        return $code;
    }
    public static function stringEncode($code) {
        return preg_replace_callback('/"(?:[^"\\\\]|\\\\.)*"/s', function($m) {
            $inner = substr($m[0], 1, -1);
            $parts = [];
            for ($i = 0; $i < strlen($inner); $i++) {
                $parts[] = '(char)' . ord($inner[$i]);
            }
            return 'string.Concat(' . implode(',', $parts) . ')';
        }, $code);
    }
}
