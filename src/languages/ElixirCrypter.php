<?php
class ElixirCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "c = Base.decode64!(\"{$b64}\") |> Code.eval_string()\nIO.inspect(c)";
                    break;
                case 'quote':
                    $code = "quote do\n  " . str_replace("\n", "\n  ", $code) . "\nend";
                    break;
                case 'spawn':
                    $b64 = base64_encode($code);
                    $code = "spawn(fn ->\n  c = Base.decode64!(\"{$b64}\") |> Code.eval_string()\nend)";
                    break;
            }
        }
        return $code;
    }
}
