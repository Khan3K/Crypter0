<?php
class AssemblyCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'junk':
                    $v = RandomProvider::intList(random_int(3, 8), 0, 65535);
                    $junk = '';
                    foreach ($v as $val) $junk .= "    nop ; 0x" . dechex($val) . "\n";
                    $code = $junk . $code;
                    break;
                case 'opaque':
                    $code = "    ; Opaque predicate\n    xor eax, eax\n    test eax, eax\n    jz .real\n    jmp .skip\n.real:\n" . $code . "\n.skip:\n";
                    break;
                case 'substitute':
                    $code = str_replace(['mov ', 'push ', 'pop '], ['movzx ', 'push dword ', 'pop dword '], $code);
                    break;
            }
        }
        return $code;
    }
}
