<?php
class SQLCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'exec':
                    $b64 = base64_encode($code);
                    $code = "EXECUTE IMMEDIATE FOR DECODE('" . $b64 . "','base64')";
                    break;
                case 'sp_executesql':
                    $b64 = base64_encode($code);
                    $code = "DECLARE @c NVARCHAR(MAX) = CAST(DECODE('" . $b64 . "', 'base64') AS NVARCHAR(MAX)); EXEC sp_executesql @c;";
                    break;
                case 'comment_wrap':
                    $code = "/* " . str_replace("\n", " */\n/* ", $code) . " */";
                    break;
            }
        }
        return $code;
    }
}
