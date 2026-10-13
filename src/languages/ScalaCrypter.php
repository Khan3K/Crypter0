<?php
class ScalaCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "import javax.script._\nval _e = new ScriptEngineManager().getEngineByName(\"js\")\n_e.eval(new String(java.util.Base64.getDecoder().decode(\"{$b64}\")))";
                    break;
                case 'toolbox':
                    $b64 = base64_encode($code);
                    $code = "import scala.reflect.runtime._\nimport scala.tools.reflect.ToolBox\nval _tb = universe.runtimeMirror(getClass.getClassLoader).mkToolBox()\nval _c = _tb.eval(_tb.parse(new String(java.util.Base64.getDecoder().decode(\"{$b64}\"))))";
                    break;
            }
        }
        return $code;
    }
}
