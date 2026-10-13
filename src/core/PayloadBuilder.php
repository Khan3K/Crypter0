<?php
class PayloadBuilder {
    public static function wrap($code, $lang, $wrapper, $keys = []) {
        switch ($lang) {
            case 'python': return self::wrapPython($code, $wrapper, $keys);
            case 'javascript': case 'typescript': return self::wrapJS($code, $wrapper, $keys);
            case 'php': return self::wrapPHP($code, $wrapper, $keys);
            case 'ruby': return self::wrapRuby($code, $wrapper, $keys);
            case 'perl': return self::wrapPerl($code, $wrapper, $keys);
            case 'bash': return self::wrapBash($code, $wrapper, $keys);
            case 'lua': return self::wrapLua($code, $wrapper, $keys);
            case 'go': return self::wrapGo($code, $wrapper, $keys);
            case 'csharp': return self::wrapCSharp($code, $wrapper, $keys);
            case 'java': return self::wrapJava($code, $wrapper, $keys);
            case 'kotlin': return self::wrapKotlin($code, $wrapper, $keys);
            case 'scala': return self::wrapScala($code, $wrapper, $keys);
            default: return $code;
        }
    }

    private static function wrapPython($code, $wrapper, $keys) {
        switch ($wrapper) {
            case 'eval':
                $s = var_export($code, true);
                return "eval({$s})";
            case 'exec':
                $s = var_export($code, true);
                return "exec({$s})";
            case 'marshal':
                return self::pythonMarshal($code);
            case 'stub':
            default:
                $b64 = base64_encode($code);
                return "import base64\nexec(base64.b64decode('" . $b64 . "').decode())";
        }
    }

    private static function pythonMarshal($code) {
        $version = trim(shell_exec("python3 -c 'import sys; print(sys.version_info[:2])' 2>/dev/null") ?: '(3, 7)');
        $headerSize = 16;
        $tmpfile = tempnam(sys_get_temp_dir(), 'pyc_');
        file_put_contents($tmpfile . '.py', $code);
        shell_exec("python3 -c \"import py_compile; py_compile.compile('" . $tmpfile . ".py', '" . $tmpfile . ".pyc', doraise=True)\" 2>/dev/null");
        if (file_exists($tmpfile . '.pyc')) {
            $data = file_get_contents($tmpfile . '.pyc');
            $b64 = base64_encode($data);
            @unlink($tmpfile . '.py');
            @unlink($tmpfile . '.pyc');
            return "import marshal,base64\n"
                 . "_d=base64.b64decode('" . $b64 . "')\n"
                 . "_c=marshal.loads(_d[" . $headerSize . ":])\n"
                 . "exec(_c)";
        }
        @unlink($tmpfile . '.py');
        $b64 = base64_encode($code);
        return "import base64\nexec(base64.b64decode('" . $b64 . "').decode())";
    }

    private static function wrapJS($code, $wrapper, $keys) {
        $s = json_encode($code);
        switch ($wrapper) {
            case 'eval': return "eval({$s})";
            case 'function': return "Function({$s})()";
            case 'iife': return "(function(){ {$code} })()";
            case 'async_wrap': return "(async()=>{ {$code} })()";
            case 'stub':
            default:
                $b64 = base64_encode($code);
                return "eval((typeof atob!=='undefined'?atob:function(b){return Buffer.from(b,'base64').toString()})('" . $b64 . "'))";
        }
    }

    private static function wrapPHP($code, $wrapper, $keys) {
        $code = preg_replace('/^<\?php\s*/i', '', trim($code));
        $code = preg_replace('/\s*\?>$/', '', $code);
        switch ($wrapper) {
            case 'eval':
                $s = var_export($code, true);
                return "<?php eval({$s}); ?>";
            case 'assert':
                $s = var_export($code, true);
                return "<?php assert({$s}); ?>";
            case 'base64':
                $b64 = base64_encode($code);
                return "<?php eval(base64_decode('" . $b64 . "')); ?>";
            case 'stub':
            default:
                $gz = base64_encode(gzcompress($code));
                return "<?php eval(gzuncompress(base64_decode('" . $gz . "'))); ?>";
        }
    }

    private static function wrapRuby($code, $wrapper, $keys) {
        $s = var_export($code, true);
        switch ($wrapper) {
            case 'eval': return "eval({$s})";
            case 'instance_eval': return "self.instance_eval({$s})";
            default: return $code;
        }
    }

    private static function wrapPerl($code, $wrapper, $keys) {
        $s = var_export($code, true);
        return "eval({$s});";
    }

    private static function wrapBash($code, $wrapper, $keys) {
        $b64 = base64_encode($code);
        switch ($wrapper) {
            case 'base64': return "eval \$(echo " . $b64 . " | base64 -d)";
            default:
                return "eval \"$(echo " . $b64 . " | base64 -d)\"";
        }
    }

    private static function wrapLua($code, $wrapper, $keys) {
        $bytes = array_map('ord', str_split($code));
        $args = implode(',', $bytes);
        return "load(string.char(" . $args . "))()";
    }

    private static function wrapGo($code, $wrapper, $keys) {
        $b64 = base64_encode($code);
        return 'package main

import (
    "encoding/base64"
    "fmt"
    "os"
)

func main() {
    c, _ := base64.StdEncoding.DecodeString("' . $b64 . '")
    fmt.Print(string(c))
    _ = os.Exit
}';
    }

    private static function wrapCSharp($code, $wrapper, $keys) {
        $b64 = base64_encode($code);
        return 'using System;
using System.Text;
class P {
    static void Main() {
        Console.Write(Encoding.UTF8.GetString(Convert.FromBase64String("' . $b64 . '")));
    }
}';
    }

    private static function wrapJava($code, $wrapper, $keys) {
        $b64 = base64_encode($code);
        return 'class P {
    public static void main(String[] a) {
        System.out.print(new String(java.util.Base64.getDecoder().decode("' . $b64 . '")));
    }
}';
    }

    private static function wrapKotlin($code, $wrapper, $keys) {
        $b64 = base64_encode($code);
        return 'fun main() {
    print(String(java.util.Base64.getDecoder().decode("' . $b64 . '")))
}';
    }

    private static function wrapScala($code, $wrapper, $keys) {
        $b64 = base64_encode($code);
        return 'object P extends App {
    print(new String(java.util.Base64.getDecoder.decode("' . $b64 . '")))
}';
    }
}
