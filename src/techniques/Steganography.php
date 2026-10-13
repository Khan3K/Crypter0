<?php
class Steganography {
    public static function process($code, $lang, $mode = 'text', $options = []) {
        switch ($mode) {
            case 'text': return self::hideInText($code);
            case 'html_comment': return self::hideInHTMLComment($code);
            case 'zero_width': return self::hideInZeroWidth($code);
            case 'unicode_lookalike': return self::hideInLookalikes($code);
            case 'whitespace': return self::hideInWhitespace($code);
            case 'css_hidden': return self::hideInCSS($code);
            default: return $code;
        }
    }

    private static function hideInText($code) {
        $b64 = base64_encode($code);
        $words = ['the','quick','brown','fox','jumps','over','lazy','dog','and','runs','fast','blue','red','green','sun','moon','star','sky','air','sea','land','fire','water','earth','wind','rain','snow','ice','fog','haze','dust','mist'];
        $stego = [];
        $idx = 0;
        for ($i = 0; $i < strlen($b64); $i += 2) {
            $chunk = substr($b64, $i, 2);
            $val = base_convert($chunk, 36, 10);
            $wordIdx = intval($val) % count($words);
            $stego[] = $words[$wordIdx];
        }
        $first = implode(' ', array_slice($stego, 0, min(8, count($stego))));
        $rest = implode(' ', array_slice($stego, min(8, count($stego))));
        return $first . "<!--" . $b64 . "-->" . $rest;
    }

    private static function hideInHTMLComment($code) {
        $b64 = base64_encode($code);
        $padding = str_repeat('<!-- ' . RandomProvider::string(40) . ' -->', random_int(3, 8));
        $stego = "<!-- STEGO:" . $b64 . " -->";
        return $padding . "\n" . $stego . "\n" . $padding;
    }

    private static function hideInZeroWidth($code) {
        $b64 = base64_encode($code);
        $bits = '';
        for ($i = 0; $i < strlen($b64); $i++) {
            $bits .= str_pad(decbin(ord($b64[$i])), 8, '0', STR_PAD_LEFT);
        }
        $result = '';
        for ($i = 0; $i < strlen($bits); $i++) {
            if ($bits[$i] === '1') {
                $result .= "\xE2\x80\x8B";
            } else {
                $result .= "\xE2\x80\x8C";
            }
        }
        return $result;
    }

    private static function hideInLookalikes($code) {
        $b64 = base64_encode($code);
        $lookalikes = [
            'a' => "\xD0\xB0", 'b' => "\xD0\xB1", 'c' => "\xD1\x86",
            'd' => "\xD0\xB4", 'e' => "\xD0\xB5", 'f' => "\xD1\x84",
            'g' => "\xD0\xB3", 'h' => "\xD1\x85", 'i' => "\xD0\xB8",
            'j' => "\xD0\xBF", 'k' => "\xD0\xBA", 'l' => "\xD0\xBB",
            'm' => "\xD0\xBC", 'n' => "\xD0\xBD", 'o' => "\xD0\xBE",
            'p' => "\xD1\x80", 'q' => "\xD1\x8F", 'r' => "\xD1\x80",
            's' => "\xD1\x81", 't' => "\xD1\x82", 'u' => "\xD1\x83",
            'v' => "\xD0\xB2", 'w' => "\xD0\xB2", 'x' => "\xD1\x85",
            'y' => "\xD1\x83", 'z' => "\xD1\x8F",
            '0' => "\xE2\x91\xB0", '1' => "\xE2\x91\xB1", '2' => "\xE2\x91\xB2",
            '3' => "\xE2\x91\xB3", '4' => "\xE2\x91\xB4", '5' => "\xE2\x91\xB5",
            '6' => "\xE2\x91\xB6", '7' => "\xE2\x91\xB7", '8' => "\xE2\x91\xB8",
            '9' => "\xE2\x91\xB9",
        ];
        $result = '';
        for ($i = 0; $i < strlen($b64); $i++) {
            $char = $b64[$i];
            $result .= $lookalikes[strtolower($char)] ?? $char;
        }
        return $result;
    }

    private static function hideInWhitespace($code) {
        $b64 = base64_encode($code);
        $bits = '';
        for ($i = 0; $i < strlen($b64); $i++) {
            $bits .= str_pad(decbin(ord($b64[$i])), 8, '0', STR_PAD_LEFT);
        }
        $result = '';
        for ($i = 0; $i < strlen($bits); $i++) {
            if ($bits[$i] === '1') $result .= "\t";
            else $result .= " ";
        }
        return $result;
    }

    private static function hideInCSS($code) {
        $b64 = base64_encode($code);
        $parts = str_split($b64, 3);
        $css = 'body{display:none}';
        foreach ($parts as $i => $part) {
            $css .= "/*" . str_repeat(' ', $i + 1) . $part . str_repeat(' ', count($parts) - $i) . "*/";
        }
        return $css;
    }
}
