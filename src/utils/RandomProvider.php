<?php
class RandomProvider {
    public static function string($length = 16, $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_') {
        if ($length <= 0) return '';
        $first = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_';
        $rest = $charset;
        $out = $first[random_int(0, strlen($first) - 1)];
        $restLen = strlen($rest) - 1;
        for ($i = 1; $i < $length; $i++) $out .= $rest[random_int(0, $restLen)];
        return $out;
    }
    public static function hexString($length = 16) {
        return bin2hex(random_bytes($length / 2));
    }
    public static function bytes($length = 16) {
        return random_bytes($length);
    }
    public static function unicodeVar() {
        $chars = 'αβγδεζηθικλμνξοπρστυφχψωабвгдежзийклмнопрстуфхцчшщъыьэюя';
        $len = mb_strlen($chars);
        $name = '';
        $name .= mb_substr($chars, random_int(0, $len - 1), 1);
        $clen = random_int(3, 8);
        for ($i = 1; $i < $clen; $i++) $name .= mb_substr($chars, random_int(0, $len - 1), 1);
        return $name;
    }
    public static function key($length = 32) {
        return bin2hex(random_bytes($length));
    }
    public static function intList($count = 5, $min = 1, $max = 9999) {
        $out = [];
        for ($i = 0; $i < $count; $i++) $out[] = random_int($min, $max);
        return $out;
    }
}
