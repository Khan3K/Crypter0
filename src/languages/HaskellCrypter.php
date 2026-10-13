<?php
class HaskellCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "import System.Process\nimport Data.ByteString.Base64\nimport Data.ByteString.Char8 as B\nmain = let c = decodeLenient (B.pack \"{$b64}\") in callCommand (B.unpack c)";
                    break;
                case 'unsafe':
                    $code = "import System.Eval.Unsafe\neval_ (\"{$code}\") :: IO (Maybe String)";
                    break;
            }
        }
        return $code;
    }
}
