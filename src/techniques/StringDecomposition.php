<?php
class StringDecomposition {
    public static function process($code, $lang, $mode = 'basic', $options = []) {
        $strings = self::extractStrings($code, $lang);
        if (empty($strings)) return $code;
        $chunkCount = match($mode) {
            'heavy' => 5, 'extreme' => 8, default => 3,
        };
        foreach ($strings as $original) {
            if (strlen($original) < 4) continue;
            $chunks = self::splitString($original, $chunkCount);
            if (count($chunks) < 2) continue;
            $reassembly = self::generateReassembly($chunks, $lang);
            $escaped = preg_quote($original, '/');
            $code = preg_replace_callback(
                '/("(' . $escaped . ')"|\'(' . $escaped . ')\')/s',
                function($m) use ($reassembly) { return $reassembly; },
                $code
            );
        }
        return $code;
    }

    private static function extractStrings($code, $lang) {
        $strings = [];
        preg_match_all('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/s', $code, $matches);
        foreach ($matches[1] as $m) { if ($m !== '' && strlen($m) > 3) $strings[] = $m; }
        foreach ($matches[2] as $m) { if ($m !== '' && strlen($m) > 3) $strings[] = $m; }
        return array_values(array_unique($strings));
    }

    private static function splitString($str, $count) {
        $len = strlen($str);
        if ($len < $count * 2) return [$str];
        $chunks = [];
        $chunkSize = intdiv($len, $count);
        for ($i = 0; $i < $count - 1; $i++) {
            $chunks[] = substr($str, $i * $chunkSize, $chunkSize);
        }
        $chunks[] = substr($str, ($count - 1) * $chunkSize);
        return $chunks;
    }

    private static function generateReassembly($chunks, $lang) {
        $parts = [];
        $quote = '"';
        foreach ($chunks as $chunk) {
            $escaped = $quote . addcslashes($chunk, $quote . '\\') . $quote;
            $parts[] = $escaped;
        }
        switch ($lang) {
            case 'python':
                return '"".join([' . implode(', ', $parts) . '])';
            case 'javascript': case 'typescript':
                return '[' . implode(',', $parts) . '].join("")';
            case 'php':
                return 'implode("",[' . implode(',', $parts) . '])';
            case 'ruby':
                return '[' . implode(',', $parts) . '].join("")';
            case 'perl':
                return 'join("",(' . implode(',', $parts) . '))';
            case 'lua':
                return 'table.concat({' . implode(',', $parts) . '})';
            case 'go':
                return 'string(append([]byte{' . implode(',', array_map(function($c) {
                    $bytes = [];
                    for ($i = 0; $i < strlen($c); $i++) $bytes[] = ord($c[$i]);
                    return implode(',', $bytes);
                }, $chunks)) . '}...))';
            case 'csharp':
                return 'string.Join("",new[]{' . implode(',', $parts) . '})';
            case 'java':
                return 'String.join("",new String[]{' . implode(',', $parts) . '})';
            case 'kotlin':
                return 'listOf(' . implode(',', $parts) . ').joinToString("")';
            case 'scala':
                return 'List(' . implode(',', $parts) . ').mkString("")';
            case 'bash':
                return implode('', $parts);
            default:
                return implode('.', $parts);
        }
    }
}
