<?php
class StringObfuscation {
    public static function encode($code, $method = 'base64', $options = []) {
        switch ($method) {
            case 'base64':
                return ['data' => CryptoProvider::base64Encode($code), 'key' => null];
            case 'base85':
                return ['data' => CryptoProvider::base85Encode($code), 'key' => null];
            case 'base58':
                return ['data' => CryptoProvider::base58Encode($code), 'key' => null];
            case 'hex':
                return ['data' => '0x' . CryptoProvider::hexEncode($code), 'key' => null];
            case 'oct':
                return ['data' => CryptoProvider::octEncode($code), 'key' => null];
            case 'bin':
                return ['data' => CryptoProvider::binEncode($code), 'key' => null];
            case 'rot13':
                return ['data' => CryptoProvider::rot13($code), 'key' => null];
            case 'rot47':
                return ['data' => CryptoProvider::rot47($code), 'key' => null];
            case 'xor':
                $result = CryptoProvider::xorEncode($code);
                return ['data' => CryptoProvider::base64Encode($result['data']), 'key' => $result['key']];
            case 'aes':
                $result = CryptoProvider::aesEncrypt($code);
                return ['data' => $result['data'], 'key' => $result['key']];
            case 'rc4':
                $result = CryptoProvider::rc4Encode($code);
                return ['data' => CryptoProvider::base64Encode($result['data']), 'key' => $result['key']];
            case 'chacha20':
                $result = CryptoProvider::chacha20Encode($code);
                return ['data' => $result['data'], 'key' => $result['key']];
            case 'dual':
                $r1 = CryptoProvider::xorEncode($code);
                $r2 = CryptoProvider::aesEncrypt($r1['data']);
                return ['data' => $r2['data'], 'key' => json_encode(['xor' => $r1['key'], 'aes' => $r2['key']])];
            case 'triple':
                $r1 = CryptoProvider::rc4Encode($code);
                $r2 = CryptoProvider::aesEncrypt($r1['data']);
                $r3 = CryptoProvider::xorEncode($r2['data']);
                return ['data' => CryptoProvider::base64Encode($r3['data']), 'key' => json_encode(['rc4' => $r1['key'], 'aes' => $r2['key'], 'xor' => $r3['key']])];
            default:
                return ['data' => CryptoProvider::base64Encode($code), 'key' => null];
        }
    }
}
