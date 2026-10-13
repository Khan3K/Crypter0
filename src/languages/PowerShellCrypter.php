<?php
class PowerShellCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'iex':
                    $code = "Invoke-Expression (" . var_export($code, true) . ")"; break;
                case 'encoded':
                    $bytes = base64_encode($code);
                    $code = "powershell -EncodedCommand " . $bytes; break;
                case 'scriptblock':
                    $code = "[ScriptBlock]::Create(" . var_export($code, true) . ").Invoke()"; break;
                case 'base64_iex':
                    $b64 = base64_encode($code);
                    $code = "IEX ([System.Text.Encoding]::UTF8.GetString([System.Convert]::FromBase64String(" . var_export($b64, true) . ")))"; break;
            }
        }
        return $code;
    }
}
