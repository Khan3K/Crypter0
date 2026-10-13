<?php
class FortranCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'exec':
                    $b64 = base64_encode($code);
                    $code = "program _exec\n  implicit none\n  character(len=512) :: cmd\n  integer :: ios\n  cmd = 'echo \"{$b64}\" | base64 -d | python3'\n  call execute_command_line(trim(cmd), exitstat=ios)\nend program";
                    break;
                case 'system':
                    $code = "call system(\"{$code}\")";
                    break;
            }
        }
        return $code;
    }
}
