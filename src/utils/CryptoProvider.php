<?php
class CryptoProvider {
    public static function base64Encode($data) { return base64_encode($data); }
    public static function base64Decode($data) { return base64_decode($data); }
    public static function base85Encode($data) {
        $out = '';
        $len = strlen($data);
        for ($i = 0; $i < $len; $i += 4) {
            $chunk = substr($data, $i, 4);
            $val = 0;
            for ($j = 0; $j < strlen($chunk); $j++) $val = $val * 256 + ord($chunk[$j]);
            $n = strlen($chunk) + 1;
            $encoded = '';
            for ($k = 0; $k < 5; $k++) { $encoded = chr(33 + ($val % 85)) . $encoded; $val = intdiv($val, 85); }
            $out .= substr($encoded, 0, $n);
        }
        return $out;
    }
    public static function base85Decode($data) {
        $out = '';
        $len = strlen($data);
        for ($i = 0; $i < $len; $i += 5) {
            $chunk = substr($data, $i, 5);
            $val = 0;
            for ($j = 0; $j < strlen($chunk); $j++) $val = $val * 85 + (ord($chunk[$j]) - 33);
            $n = strlen($chunk) - 1;
            for ($k = 0; $k < $n; $k++) { $out .= chr(($val >> (8 * ($n - 1 - $k))) & 0xFF); }
        }
        return $out;
    }
    public static function xorEncode($data, $key = null) {
        if ($key === null) $key = RandomProvider::string(random_int(8, 64));
        $out = '';
        $klen = strlen($key);
        for ($i = 0; $i < strlen($data); $i++) $out .= chr(ord($data[$i]) ^ ord($key[$i % $klen]));
        return ['data' => $out, 'key' => $key];
    }
    public static function xorDecode($data, $key) {
        $out = '';
        $klen = strlen($key);
        for ($i = 0; $i < strlen($data); $i++) $out .= chr(ord($data[$i]) ^ ord($key[$i % $klen]));
        return $out;
    }
    public static function aesEncrypt($data, $key = null) {
        if ($key === null) $key = bin2hex(random_bytes(16));
        $keyBytes = substr(hash('sha256', $key, true), 0, 32);
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $keyBytes, OPENSSL_RAW_DATA, $iv);
        return ['data' => base64_encode($iv . $encrypted), 'key' => $key];
    }
    public static function aesDecrypt($data, $key) {
        $keyBytes = substr(hash('sha256', $key, true), 0, 32);
        $raw = base64_decode($data);
        $iv = substr($raw, 0, 16);
        $encrypted = substr($raw, 16);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $keyBytes, OPENSSL_RAW_DATA, $iv);
    }
    public static function rc4Encode($data, $key = null) {
        if ($key === null) $key = RandomProvider::string(random_int(8, 64));
        $s = range(0, 255);
        $klen = strlen($key);
        $j = 0;
        for ($i = 0; $i < 256; $i++) { $j = ($j + $s[$i] + ord($key[$i % $klen])) % 256; $tmp = $s[$i]; $s[$i] = $s[$j]; $s[$j] = $tmp; }
        $out = '';
        $i = 0; $j = 0;
        for ($n = 0; $n < strlen($data); $n++) { $i = ($i + 1) % 256; $j = ($j + $s[$i]) % 256; $tmp = $s[$i]; $s[$i] = $s[$j]; $s[$j] = $tmp; $out .= chr(ord($data[$n]) ^ $s[($s[$i] + $s[$j]) % 256]); }
        return ['data' => $out, 'key' => $key];
    }
    public static function rc4Decode($data, $key) {
        $result = self::rc4Encode($data, $key);
        return $result['data'];
    }
    public static function rot13($data) { return str_rot13($data); }
    public static function rot47($data) {
        $out = '';
        for ($i = 0; $i < strlen($data); $i++) {
            $c = ord($data[$i]);
            if ($c >= 33 && $c <= 126) $out .= chr(33 + (($c - 33 + 47) % 94));
            else $out .= $data[$i];
        }
        return $out;
    }
    public static function hexEncode($data) { return bin2hex($data); }
    public static function hexDecode($data) { return hex2bin($data); }
    public static function octEncode($data) {
        $out = '';
        for ($i = 0; $i < strlen($data); $i++) $out .= '\\' . decoct(ord($data[$i]));
        return $out;
    }
    public static function binEncode($data) {
        $out = '';
        for ($i = 0; $i < strlen($data); $i++) $out .= sprintf('%08b', ord($data[$i]));
        return $out;
    }
    public static function base58Encode($data) {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $val = gmp_import($data);
        $out = '0';
        $base = gmp_init(58);
        while (gmp_cmp($val, 0) > 0) {
            $rem = gmp_intval(gmp_mod($val, $base));
            $out = $alphabet[$rem] . $out;
            $val = gmp_div_q($val, $base);
        }
        return ltrim($out, '0') ?: '1';
    }
    public static function chachaEncode($data, $key = null) {
        if ($key === null) $key = bin2hex(random_bytes(16));
        if (function_exists('sodium_crypto_stream_chacha20')) {
            $nonce = random_bytes(SODIUM_CRYPTO_STREAM_CHACHA20_NONCEBYTES);
            $keyBytes = substr(hash('sha256', $key, true), 0, SODIUM_CRYPTO_STREAM_CHACHA20_KEYBYTES);
            $encrypted = sodium_crypto_stream_chacha20_xor($data, $nonce, $keyBytes);
            sodium_memzero($keyBytes);
            return ['data' => base64_encode($nonce . $encrypted), 'key' => $key];
        }
        $keyBytes = substr(hash('sha256', $key, true), 0, 32);
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'aes-256-ctr', $keyBytes, OPENSSL_RAW_DATA, $iv);
        return ['data' => base64_encode($iv . $encrypted), 'key' => $key];
    }
    public static function chachaDecode($data, $key) {
        $raw = base64_decode($data);
        if (function_exists('sodium_crypto_stream_chacha20')) {
            $nonce = substr($raw, 0, SODIUM_CRYPTO_STREAM_CHACHA20_NONCEBYTES);
            $encrypted = substr($raw, SODIUM_CRYPTO_STREAM_CHACHA20_NONCEBYTES);
            $keyBytes = substr(hash('sha256', $key, true), 0, SODIUM_CRYPTO_STREAM_CHACHA20_KEYBYTES);
            $result = sodium_crypto_stream_chacha20_xor($encrypted, $nonce, $keyBytes);
            sodium_memzero($keyBytes);
            return $result;
        }
        $keyBytes = substr(hash('sha256', $key, true), 0, 32);
        $iv = substr($raw, 0, 16);
        $encrypted = substr($raw, 16);
        return openssl_decrypt($encrypted, 'aes-256-ctr', $keyBytes, OPENSSL_RAW_DATA, $iv);
    }
    public static function applyMethod($method, $data, &$key = null) {
        $method = strtolower($method);
        switch ($method) {
            case 'base64': return ['data' => self::base64Encode($data), 'key' => null];
            case 'base85': return ['data' => self::base85Encode($data), 'key' => null];
            case 'base58': return ['data' => self::base58Encode($data), 'key' => null];
            case 'hex': return ['data' => self::hexEncode($data), 'key' => null];
            case 'oct': return ['data' => self::octEncode($data), 'key' => null];
            case 'bin': return ['data' => self::binEncode($data), 'key' => null];
            case 'rot13': return ['data' => self::rot13($data), 'key' => null];
            case 'rot47': return ['data' => self::rot47($data), 'key' => null];
            case 'xor': return self::xorEncode($data, $key);
            case 'aes256': case 'aes-256': case 'aes': return self::aesEncrypt($data, $key);
            case 'rc4': return self::rc4Encode($data, $key);
            case 'chacha20': case 'chacha': return self::chachaEncode($data, $key);
            default: return ['data' => $data, 'key' => null];
        }
    }
    public static function decodeMethod($method, $data, $key = null) {
        $method = strtolower($method);
        switch ($method) {
            case 'base64': return self::base64Decode($data);
            case 'base85': return self::base85Decode($data);
            case 'hex': return self::hexDecode($data);
            case 'rot13': return self::rot13($data);
            case 'rot47': return self::rot47($data);
            case 'xor': return $key ? self::xorDecode($data, $key) : $data;
            case 'aes256': case 'aes-256': case 'aes': return $key ? self::aesDecrypt($data, $key) : $data;
            case 'rc4': return $key ? self::rc4Decode($data, $key) : $data;
            case 'chacha20': case 'chacha': return $key ? self::chachaDecode($data, $key) : $data;
            default: return $data;
        }
    }
}
