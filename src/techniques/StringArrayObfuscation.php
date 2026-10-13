<?php
class StringArrayObfuscation {
    public static function process($code, $lang, $mode = 'xor', $options = []) {
        $strings = self::extractStrings($code);
        if (count($strings) < 1) return $code;

        $method = $options['method'] ?? $mode;
        $shuffle = ($options['shuffle'] ?? '1') === '1' || ($options['shuffle'] ?? '1') === true;
        $minLength = intval($options['min_length'] ?? 2);

        $filtered = array_values(array_filter($strings, function($s) use ($minLength) {
            return strlen($s) >= $minLength;
        }));
        if (empty($filtered)) return $code;

        $indices = array_keys($filtered);
        if ($shuffle) {
            $shuffled = $indices;
            for ($i = count($shuffled) - 1; $i > 0; $i--) {
                $j = random_int(0, $i);
                [$shuffled[$i], $shuffled[$j]] = [$shuffled[$j], $shuffled[$i]];
            }
            $map = array_flip($shuffled);
        } else {
            $map = array_flip($indices);
        }

        $encryptedArr = [];
        $key = RandomProvider::hexString(16);
        foreach ($filtered as $str) {
            $enc = self::encryptEntry($str, $method, $key);
            $encryptedArr[] = $enc;
        }

        $stub = self::generateStub($encryptedArr, $key, $method, $lang);
        if (!$stub) return $code;

        foreach ($filtered as $idx => $original) {
            $mappedIdx = $map[$idx] ?? $idx;
            $call = self::generateLookup($mappedIdx, $lang);
            $escaped = preg_quote($original, '/');
            $code = preg_replace_callback(
                '/("(' . $escaped . ')"|\'(' . $escaped . ')\')/s',
                function($m) use ($call) { return $call; },
                $code
            );
        }

        return $stub . "\n" . $code;
    }

    private static function extractStrings($code) {
        $strings = [];
        preg_match_all('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/s', $code, $matches);
        foreach ($matches[1] as $m) { if ($m !== '' && strlen($m) > 0) $strings[] = $m; }
        foreach ($matches[2] as $m) { if ($m !== '' && strlen($m) > 0) $strings[] = $m; }
        return array_values(array_unique($strings));
    }

    private static function encryptEntry($str, $method, $key) {
        switch ($method) {
            case 'xor':
                $enc = '';
                for ($i = 0; $i < strlen($str); $i++) $enc .= chr(ord($str[$i]) ^ ord($key[$i % 16]));
                return base64_encode($enc);
            case 'aes':
                $result = CryptoProvider::aesEncrypt($str, $key);
                return $result['data'];
            default:
                return base64_encode($str);
        }
    }

    private static function generateStub($arr, $key, $method, $lang) {
        $jsonArr = json_encode($arr);
        $b64Arr = base64_encode($jsonArr);
        switch ($lang) {
            case 'python':
                $kHex = bin2hex($key);
                if ($method === 'xor') {
                    return '_sa=[' . self::pyArrayDecrypt($b64Arr, $kHex) . '];';
                }
                return '_sa=json.loads(__import__("base64").b64decode("' . $b64Arr . '").decode());';
            case 'javascript': case 'typescript':
                if ($method === 'xor') {
                    return 'var _sa=' . self::jsArrayDecrypt($b64Arr, $key) . ';';
                }
                return 'var _sa=JSON.parse((typeof atob!=="undefined"?atob:function(b){return Buffer.from(b,"base64").toString()})("' . $b64Arr . '"));';
            case 'php':
                if ($method === 'xor') {
                    return '<?php $_sa=' . self::phpArrayDecrypt($b64Arr, $key) . ';?>';
                }
                return '<?php $_sa=json_decode(base64_decode("' . $b64Arr . '"),true);?>';
            default:
                return null;
        }
    }

    private static function pyArrayDecrypt($b64Arr, $kHex) {
        return 'list(map(lambda x:x^int(__import__("random").seed(int("' . $kHex . '",__import__("random").seed() or 16))or 0),range(0)))' .
            ' if False else [__import__("base64").b64decode("' . $b64Arr . '").decode()]' ;
    }

    private static function jsArrayDecrypt($b64Arr, $key) {
        return '(function(){var _a=typeof atob!=="undefined"?atob:function(b){return Buffer.from(b,"base64").toString()};var _d=_a("' . $b64Arr . '"),_k="' . $key . '",_r="";' .
            'for(var i=0;i<_d.length;i++)_r+=String.fromCharCode(_d.charCodeAt(i)^_k.charCodeAt(i%_k.length));' .
            'return JSON.parse(_r)})()';
    }

    private static function phpArrayDecrypt($b64Arr, $key) {
        return '(function(){$_d=base64_decode("' . $b64Arr . '");$_k="' . $key . '";$_r="";' .
            'for($_i=0;$_i<strlen($_d);$_i++)$_r.=chr(ord($_d[$_i])^ord($_k[$_i%strlen($_k)]));' .
            'return json_decode($_r,true);})()';
    }

    private static function generateLookup($index, $lang) {
        switch ($lang) {
            case 'python': return '_sa[' . $index . ']';
            case 'javascript': case 'typescript': return '_sa[' . $index . ']';
            case 'php': return '$_sa[' . $index . ']';
            default: return '_sa[' . $index . ']';
        }
    }
}
