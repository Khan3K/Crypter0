<?php
class HTMLCrypter {
    public static function process($code, $techniques = []) {
        foreach ($techniques as $t) {
            switch ($t) {
                case 'eval':
                    $b64 = base64_encode($code);
                    $code = "<script>document.write(atob('{$b64}'))</script>";
                    break;
                case 'noscript':
                    $b64 = base64_encode($code);
                    $code = "<noscript><meta http-equiv=\"refresh\" content=\"0;url=data:text/html;base64,{$b64}\"></noscript>";
                    break;
                case 'encapsulate':
                    $b64 = base64_encode($code);
                    $code = "<div id=\"_e\" style=\"display:none\">{$b64}</div><script>document.write(atob(document.getElementById('_e').textContent))</script>";
                    break;
                case 'minify':
                    $code = preg_replace('/\s+/', ' ', $code);
                    $code = preg_replace('/>\s+</', '><', $code);
                    break;
                case 'css_hide':
                    $code = "<style>.c{display:none}</style><span class=\"c\">" . htmlspecialchars($code) . "</span>";
                    break;
            }
        }
        return $code;
    }
}
