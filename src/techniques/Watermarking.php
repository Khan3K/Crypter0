<?php
class Watermarking {
    public static function process($code, $lang, $mode = 'basic', $options = []) {
        $watermark = $options['watermark'] ?? RandomProvider::string(16);
        $userId = $options['user_id'] ?? RandomProvider::string(8);
        $timestamp = $options['timestamp'] ?? time();
        $wmType = $options['type'] ?? $mode;

        switch ($wmType) {
            case 'basic': return self::basicWatermark($code, $lang, $watermark);
            case 'binary': return self::binaryWatermark($code, $lang, $watermark);
            case 'invisible': return self::invisibleWatermark($code, $lang, $watermark);
            case 'metadata': return self::metadataWatermark($code, $lang, $watermark, $userId, $timestamp);
            case 'hmac': return self::hmacWatermark($code, $lang, $watermark);
            case 'trace': return self::traceWatermark($code, $lang, $watermark);
            default: return $code;
        }
    }

    private static function comment($lang, $text) {
        switch ($lang) {
            case 'python': return "# " . $text;
            case 'javascript': case 'typescript': return "/* " . $text . " */";
            case 'php': return "// " . $text;
            case 'ruby': return "# " . $text;
            case 'perl': return "# " . $text;
            case 'bash': case 'powershell': case 'batch': return "# " . $text;
            case 'lua': return "-- " . $text;
            case 'go': case 'rust': case 'csharp': case 'java': case 'kotlin': case 'swift':
            case 'dart': case 'scala': case 'groovy': case 'typescript':
                return "// " . $text;
            case 'haskell': return "-- " . $text;
            case 'elixir': case 'erlang': return "# " . $text;
            case 'clojure': return ";; " . $text;
            case 'html': return "<!-- " . $text . " -->";
            case 'css': return "/* " . $text . " */";
            default: return "// " . $text;
        }
    }

    private static function basicWatermark($code, $lang, $wm) {
        $hash = hash('sha256', $wm);
        $c1 = self::comment($lang, 'Watermark: ' . base64_encode($wm));
        $c2 = self::comment($lang, 'Hash: ' . substr($hash, 0, 16));
        return $c1 . "\n" . $c2 . "\n" . $code;
    }

    private static function binaryWatermark($code, $lang, $wm) {
        $hash = hash('crc32', $wm);
        $code .= "\n" . self::comment($lang, 'CRC:' . $hash) . "\n";
        $chars = str_split($code);
        for ($i = 0; $i < min(8, strlen($hash)); $i++) {
            $pos = ord($hash[$i]) % max(1, count($chars) - 1);
            $chars[$pos] .= chr(0xFEFF);
        }
        return implode('', $chars);
    }

    private static function invisibleWatermark($code, $lang, $wm) {
        $hash = hash('sha256', $wm);
        $chars = [];
        for ($i = 0; $i < strlen($hash) && $i < 64; $i++) {
            $chars[] = chr(0x200B + (ord($hash[$i]) % 3));
        }
        $invisible = implode('', $chars);
        $pos = strlen($code) / 2;
        return substr($code, 0, $pos) . $invisible . substr($code, $pos);
    }

    private static function metadataWatermark($code, $lang, $wm, $userId, $ts) {
        $meta = json_encode(['wm' => base64_encode($wm), 'uid' => $userId, 'ts' => $ts, 'h' => hash('sha256', $wm . $userId . $ts)]);
        $encoded = base64_encode($meta);
        return $code . "\n" . self::comment($lang, 'META:' . $encoded) . "\n";
    }

    private static function hmacWatermark($code, $lang, $wm) {
        $hmac = hash_hmac('sha256', $code, $wm);
        return $code . "\n" . self::comment($lang, 'HMAC:' . $hmac) . "\n";
    }

    private static function traceWatermark($code, $lang, $wm) {
        $lines = explode("\n", $code);
        $hash = hash('sha256', $wm);
        $positions = [];
        for ($i = 0; $i < min(8, strlen($hash)); $i++) {
            $pos = ord($hash[$i]) % max(1, count($lines));
            $positions[] = $pos;
        }
        $suffix = '_WM' . RandomProvider::string(4);
        foreach ($positions as $pos) {
            if (isset($lines[$pos])) {
                switch ($lang) {
                    case 'python': case 'ruby': case 'perl': case 'bash': case 'elixir': case 'erlang':
                        $lines[$pos] .= " #_" . $suffix;
                        break;
                    case 'lua': case 'haskell':
                        $lines[$pos] .= " --_" . $suffix;
                        break;
                    default:
                        $lines[$pos] .= " //_" . $suffix;
                }
            }
        }
        return implode("\n", $lines);
    }
}
