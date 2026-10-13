<?php
class KotlinCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "import javax.script.*\nval _e = ScriptEngineManager().getEngineByName(\"js\")\n_e.eval(String(java.util.Base64.getDecoder().decode(\"{$b64}\")))";
                    break;
                case 'run':
                    $b64 = base64_encode($code);
                    $code = "Thread { Runtime.getRuntime().exec(arrayOf(\"kotlin\", \"-e\", String(java.util.Base64.getDecoder().decode(\"{$b64}\")))) }.start()";
                    break;
                case 'reflection':
                    $code = "Class.forName(\"_D\").declaredMethods.first { it.name == \"_m\" }.apply { isAccessible = true }.invoke(null)";
                    break;
            }
        }
        return $code;
    }
}
