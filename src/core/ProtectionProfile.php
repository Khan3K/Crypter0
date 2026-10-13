<?php
class ProtectionProfile {
    public static function get($level) {
        return self::$levels[min(max($level, 1), 12)] ?? self::$levels[1];
    }

    public static $levels = [
        1 => ['name' => 'Minimal', 'pipeline' => [
            ['category' => 'wrapper', 'technique' => 'stub', 'options' => []],
        ]],
        2 => ['name' => 'Light', 'pipeline' => [
            ['category' => 'transform', 'technique' => 'simple', 'options' => []],
            ['category' => 'wrapper', 'technique' => 'stub', 'options' => []],
        ]],
        3 => ['name' => 'Basic', 'pipeline' => [
            ['category' => 'transform', 'technique' => 'simple', 'options' => []],
            ['category' => 'deadcode', 'technique' => 'light', 'options' => []],
            ['category' => 'wrapper', 'technique' => 'stub', 'options' => []],
        ]],
        4 => ['name' => 'Standard', 'pipeline' => [
            ['category' => 'transform', 'technique' => 'simple', 'options' => []],
            ['category' => 'deadcode', 'technique' => 'medium', 'options' => []],
            ['category' => 'controlflow', 'technique' => 'basic', 'options' => []],
            ['category' => 'wrapper', 'technique' => 'stub', 'options' => []],
        ]],
        5 => ['name' => 'Enhanced', 'pipeline' => [
            ['category' => 'transform', 'technique' => 'simple', 'options' => []],
            ['category' => 'deadcode', 'technique' => 'medium', 'options' => []],
            ['category' => 'controlflow', 'technique' => 'basic', 'options' => []],
            ['category' => 'instruct', 'technique' => 'basic', 'options' => []],
            ['category' => 'wrapper', 'technique' => 'stub', 'options' => []],
        ]],
        6 => ['name' => 'Advanced', 'pipeline' => [
            ['category' => 'transform', 'technique' => 'simple', 'options' => []],
            ['category' => 'deadcode', 'technique' => 'heavy', 'options' => []],
            ['category' => 'controlflow', 'technique' => 'basic', 'options' => []],
            ['category' => 'instruct', 'technique' => 'basic', 'options' => []],
            ['category' => 'opaque', 'technique' => 'basic', 'options' => []],
            ['category' => 'strarray', 'technique' => 'xor', 'options' => ['method' => 'xor']],
            ['category' => 'wrapper', 'technique' => 'stub', 'options' => []],
        ]],
        7 => ['name' => 'Enterprise', 'pipeline' => [
            ['category' => 'transform', 'technique' => 'simple', 'options' => []],
            ['category' => 'deadcode', 'technique' => 'heavy', 'options' => []],
            ['category' => 'controlflow', 'technique' => 'basic', 'options' => []],
            ['category' => 'instruct', 'technique' => 'full', 'options' => []],
            ['category' => 'opaque', 'technique' => 'medium', 'options' => []],
            ['category' => 'strencrypt', 'technique' => 'xor', 'options' => ['cipher' => 'xor', 'layers' => '1']],
            ['category' => 'decompose', 'technique' => 'basic', 'options' => []],
            ['category' => 'anti', 'technique' => 'debug', 'options' => []],
            ['category' => 'wrapper', 'technique' => 'stub', 'options' => []],
        ]],
        8 => ['name' => 'Hardened', 'pipeline' => [
            ['category' => 'transform', 'technique' => 'simple', 'options' => []],
            ['category' => 'deadcode', 'technique' => 'heavy', 'options' => []],
            ['category' => 'controlflow', 'technique' => 'full', 'options' => []],
            ['category' => 'instruct', 'technique' => 'full', 'options' => []],
            ['category' => 'opaque', 'technique' => 'medium', 'options' => []],
            ['category' => 'strencrypt', 'technique' => 'xor', 'options' => ['cipher' => 'xor', 'layers' => '2']],
            ['category' => 'antiai', 'technique' => 'basic', 'options' => []],
            ['category' => 'polymorphic', 'technique' => 'full', 'options' => []],
            ['category' => 'anti', 'technique' => 'debug', 'options' => []],
            ['category' => 'watermark', 'technique' => 'basic', 'options' => []],
            ['category' => 'wrapper', 'technique' => 'stub', 'options' => []],
        ]],
        9 => ['name' => 'Extreme', 'pipeline' => [
            ['category' => 'transform', 'technique' => 'simple', 'options' => []],
            ['category' => 'deadcode', 'technique' => 'heavy', 'options' => []],
            ['category' => 'controlflow', 'technique' => 'full', 'options' => []],
            ['category' => 'instruct', 'technique' => 'full', 'options' => []],
            ['category' => 'opaque', 'technique' => 'heavy', 'options' => []],
            ['category' => 'strencrypt', 'technique' => 'xor', 'options' => ['cipher' => 'xor', 'layers' => '2']],
            ['category' => 'antiai', 'technique' => 'confuse', 'options' => []],
            ['category' => 'polymorphic', 'technique' => 'full', 'options' => []],
            ['category' => 'anti', 'technique' => 'debug', 'options' => []],
            ['category' => 'anti', 'technique' => 'tamper', 'options' => []],
            ['category' => 'watermark', 'technique' => 'hmac', 'options' => []],
            ['category' => 'wrapper', 'technique' => 'stub', 'options' => []],
        ]],
        10 => ['name' => 'Maximum', 'pipeline' => [
            ['category' => 'transform', 'technique' => 'simple', 'options' => []],
            ['category' => 'deadcode', 'technique' => 'heavy', 'options' => []],
            ['category' => 'controlflow', 'technique' => 'full', 'options' => []],
            ['category' => 'instruct', 'technique' => 'full', 'options' => []],
            ['category' => 'opaque', 'technique' => 'heavy', 'options' => []],
            ['category' => 'strencrypt', 'technique' => 'xor', 'options' => ['cipher' => 'xor', 'layers' => '3']],
            ['category' => 'antiai', 'technique' => 'confuse', 'options' => []],
            ['category' => 'antiai', 'technique' => 'noise_inject', 'options' => []],
            ['category' => 'polymorphic', 'technique' => 'full', 'options' => []],
            ['category' => 'anti', 'technique' => 'debug', 'options' => []],
            ['category' => 'anti', 'technique' => 'tamper', 'options' => []],
            ['category' => 'watermark', 'technique' => 'hmac', 'options' => []],
            ['category' => 'wrapper', 'technique' => 'stub', 'options' => []],
        ]],
        11 => ['name' => 'Paragon', 'pipeline' => [
            ['category' => 'transform', 'technique' => 'simple', 'options' => []],
            ['category' => 'deadcode', 'technique' => 'heavy', 'options' => []],
            ['category' => 'controlflow', 'technique' => 'full', 'options' => []],
            ['category' => 'instruct', 'technique' => 'full', 'options' => []],
            ['category' => 'opaque', 'technique' => 'extreme', 'options' => []],
            ['category' => 'strencrypt', 'technique' => 'xor', 'options' => ['cipher' => 'xor', 'layers' => '3']],
            ['category' => 'antiai', 'technique' => 'full', 'options' => []],
            ['category' => 'polymorphic', 'technique' => 'full', 'options' => []],
            ['category' => 'anti', 'technique' => 'debug', 'options' => []],
            ['category' => 'anti', 'technique' => 'tamper', 'options' => []],
            ['category' => 'anti', 'technique' => 'vm', 'options' => []],
            ['category' => 'watermark', 'technique' => 'trace', 'options' => []],
            ['category' => 'wrapper', 'technique' => 'stub', 'options' => []],
        ]],
        12 => ['name' => 'Ultimate', 'pipeline' => [
            ['category' => 'transform', 'technique' => 'simple', 'options' => []],
            ['category' => 'deadcode', 'technique' => 'heavy', 'options' => []],
            ['category' => 'controlflow', 'technique' => 'full', 'options' => []],
            ['category' => 'instruct', 'technique' => 'full', 'options' => []],
            ['category' => 'opaque', 'technique' => 'extreme', 'options' => []],
            ['category' => 'strencrypt', 'technique' => 'xor', 'options' => ['cipher' => 'xor', 'layers' => '3']],
            ['category' => 'antiai', 'technique' => 'full', 'options' => []],
            ['category' => 'polymorphic', 'technique' => 'full', 'options' => []],
            ['category' => 'anti', 'technique' => 'debug', 'options' => []],
            ['category' => 'anti', 'technique' => 'tamper', 'options' => []],
            ['category' => 'anti', 'technique' => 'vm', 'options' => []],
            ['category' => 'watermark', 'technique' => 'trace', 'options' => []],
            ['category' => 'wrapper', 'technique' => 'stub', 'options' => []],
        ]],
    ];

    public static function getForLang($level, $lang) {
        $profile = self::get($level);
        $pipeline = $profile['pipeline'];

        $compiledLangs = ['go', 'csharp', 'java', 'kotlin', 'scala'];
        $noEvalLangs = ['go', 'csharp', 'java', 'kotlin', 'scala', 'ruby', 'perl', 'lua', 'bash'];

        $filtered = [];
        foreach ($pipeline as $step) {
            $cat = $step['category'];

            if (in_array($lang, $compiledLangs) && in_array($cat, ['codevir', 'selfmod'])) continue;

            if (in_array($lang, $noEvalLangs) && $cat === 'split') continue;

            $filtered[] = $step;
        }

        return ['name' => $profile['name'], 'pipeline' => $filtered];
    }
}
