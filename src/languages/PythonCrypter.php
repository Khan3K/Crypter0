<?php
class PythonCrypter {
    public static function process($code, $lang = 'python', $mode = 'marshal', $options = []) {
        switch ($mode) {
            case 'marshal': return self::marshalEncrypt($code);
            case 'compile_exec': return self::compileExec($code);
            case 'import_hook': return self::importHook($code);
            case 'bytecode': return self::bytecodeInject($code);
            default: return $code;
        }
    }

    public static function marshalEncrypt($code) {
        $tmpFile = tempnam(sys_get_temp_dir(), 'pyc_') . '.py';
        file_put_contents($tmpFile, $code);
        $pyScript = "import marshal, base64, zlib, py_compile\npy_compile.compile(" . escapeshellarg($tmpFile) . ", doraise=True)\nwith open(" . escapeshellarg($tmpFile . 'c') . ", 'rb') as f: data = f.read()\nimport os; os.remove(" . escapeshellarg($tmpFile . 'c') . ")\nprint(base64.b64encode(zlib.compress(data)).decode())";
        $result = shell_exec("python3 -c " . escapeshellarg($pyScript) . " 2>/dev/null");
        @unlink($tmpFile);
        if ($result && trim($result)) {
            $enc = trim($result);
            return "import marshal, base64, zlib\nexec(marshal.loads(zlib.decompress(base64.b64decode(" . var_export($enc, true) . "))[16:]))";
        }
        return "exec(" . var_export($code, true) . ")";
    }

    public static function compileExec($code) {
        $b64 = base64_encode($code);
        return "import base64\nexec(compile(base64.b64decode(" . var_export($b64, true) . ").decode('utf-8'), '<run>', 'exec'))";
    }

    public static function importHook($code) {
        $b64 = base64_encode($code);
        return "import sys, base64, types, importlib.machinery\n_c = compile(base64.b64decode(" . var_export($b64, true) . "), '<dynamic>', 'exec')\n_m = types.ModuleType('_d')\n_spec = importlib.machinery.ModuleSpec('_d', None)\n_m.__spec__ = _spec\nexec(_c, _m.__dict__)";
    }

    public static function bytecodeInject($code) {
        return self::marshalEncrypt($code);
    }

    public static function obfuscateLiterals($code) {
        $code = preg_replace_callback('/"[^"]*"/', function($m) {
            $inner = $m[0];
            $parts = [];
            for ($i = 1; $i < strlen($inner) - 1; $i++) {
                $parts[] = 'chr(' . ord($inner[$i]) . ')';
            }
            return implode('+', $parts) ?: 'chr(0)';
        }, $code);
        $code = preg_replace_callback("/'[^']*'/", function($m) {
            $inner = $m[0];
            $parts = [];
            for ($i = 1; $i < strlen($inner) - 1; $i++) {
                $parts[] = 'chr(' . ord($inner[$i]) . ')';
            }
            return implode('+', $parts) ?: 'chr(0)';
        }, $code);
        return $code;
    }
}
