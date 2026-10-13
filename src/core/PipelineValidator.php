<?php
class PipelineValidator {
    private static $compat = [
        'python'     => ['transform','deadcode','controlflow','strencrypt','codevir','selfmod','instruct','opaque','antiai','watermark','polymorphic','split','stegano','codeenc','anti','marshal','wrapper','strarray','decompose','autoimport'],
        'javascript' => ['transform','deadcode','controlflow','strencrypt','codevir','selfmod','instruct','opaque','antiai','watermark','polymorphic','split','stegano','codeenc','anti','wrapper','strarray','decompose','autoimport'],
        'typescript' => ['transform','deadcode','controlflow','strencrypt','codevir','selfmod','instruct','opaque','antiai','watermark','polymorphic','split','stegano','codeenc','anti','wrapper','strarray','decompose','autoimport'],
        'php'        => ['transform','deadcode','controlflow','strencrypt','codevir','selfmod','instruct','opaque','antiai','watermark','polymorphic','split','stegano','codeenc','anti','wrapper','strarray','decompose'],
        'ruby'       => ['transform','deadcode','strencrypt','instruct','opaque','antiai','watermark','polymorphic','stegano','codeenc','anti','wrapper','autoimport'],
        'perl'       => ['transform','deadcode','strencrypt','instruct','opaque','antiai','watermark','polymorphic','stegano','codeenc','anti','wrapper','autoimport'],
        'lua'        => ['transform','deadcode','strencrypt','instruct','opaque','antiai','watermark','polymorphic','stegano','codeenc','anti','wrapper'],
        'go'         => ['transform','deadcode','controlflow','strencrypt','instruct','opaque','antiai','watermark','polymorphic','codeenc','anti','wrapper'],
        'csharp'     => ['transform','deadcode','controlflow','strencrypt','instruct','opaque','antiai','watermark','polymorphic','codeenc','anti','wrapper'],
        'java'       => ['transform','deadcode','controlflow','strencrypt','instruct','opaque','antiai','watermark','polymorphic','codeenc','anti','wrapper'],
        'kotlin'     => ['transform','deadcode','controlflow','strencrypt','instruct','opaque','antiai','watermark','polymorphic','codeenc','anti','wrapper'],
        'scala'      => ['transform','deadcode','controlflow','strencrypt','instruct','opaque','antiai','watermark','polymorphic','codeenc','anti','wrapper'],
        'bash'       => ['transform','deadcode','strencrypt','instruct','opaque','watermark','polymorphic','codeenc','anti','wrapper'],
    ];

    private static $cipherCompat = [
        'python'     => ['xor','aes','rc4','chacha','reverse','caesar','base64','envkey'],
        'javascript' => ['xor','aes','rc4','chacha','reverse','caesar','base64','envkey'],
        'typescript' => ['xor','aes','rc4','chacha','reverse','caesar','base64','envkey'],
        'php'        => ['xor','aes','rc4','chacha','reverse','caesar','base64','envkey'],
        'ruby'       => ['xor','aes','rc4','chacha','reverse','caesar','base64'],
        'perl'       => ['xor','reverse','caesar','base64'],
        'lua'        => ['xor','reverse','base64'],
        'go'         => ['xor','reverse','caesar','base64'],
        'csharp'     => ['xor','reverse','base64'],
        'java'       => ['xor','reverse','base64'],
        'kotlin'     => ['xor','reverse','base64'],
        'scala'      => ['xor','reverse','base64'],
        'bash'       => ['xor','reverse','base64'],
    ];

    private static $orderRules = [
        'transform' => ['before' => ['strencrypt', 'codeenc', 'strarray']],
        'deadcode'  => ['before' => ['strencrypt', 'codeenc', 'strarray']],
        'instruct'  => ['before' => ['strencrypt', 'codeenc', 'strarray']],
    ];

    public static function validate($pipeline, $lang) {
        $warnings = [];
        $suggestions = [];
        $compat = self::$compat[$lang] ?? self::$compat['python'] ?? [];
        $wrapperCount = 0;
        $hasStringEncrypt = false;

        foreach ($pipeline as $i => $step) {
            $cat = $step['category'] ?? $step['cat'] ?? '';
            $tech = $step['technique'] ?? $step['tech'] ?? '';
            $opts = $step['options'] ?? $step['opts'] ?? [];

            if (!in_array($cat, $compat)) {
                $warnings[] = "Step " . ($i + 1) . " ({$cat}/{$tech}): Not supported for {$lang}";
            }

            if ($cat === 'strencrypt' && isset($opts['cipher'])) {
                $cipherCompat = self::$cipherCompat[$lang] ?? [];
                if (!in_array($opts['cipher'], $cipherCompat)) {
                    $warnings[] = "Step " . ($i + 1) . ": Cipher '{$opts['cipher']}' not supported for {$lang}";
                }
            }

            if ($cat === 'wrapper') {
                $wrapperCount++;
                if ($wrapperCount > 1) {
                    $warnings[] = "Multiple wrapper steps detected — only the last one will be applied";
                }
            }

            if ($cat === 'strencrypt') $hasStringEncrypt = true;

            if ($cat === 'anti' && isset($opts['callback']) && $opts['callback']) {
                if (!isset($opts['callback_url'])) {
                    $suggestions[] = "Consider setting a custom callback URL (default: localhost:1337)";
                }
            }
        }

        if (!$hasStringEncrypt && in_array($lang, ['python', 'javascript', 'php'])) {
            $suggestions[] = "Consider adding string encryption for stronger obfuscation";
        }

        if (empty($pipeline)) {
            $warnings[] = "Pipeline is empty — no techniques will be applied";
        }

        $hasWrapper = false;
        foreach ($pipeline as $step) {
            $cat = $step['category'] ?? $step['cat'] ?? '';
            if ($cat === 'wrapper' || $cat === 'marshal') { $hasWrapper = true; break; }
        }
        if (!$hasWrapper && !empty($pipeline)) {
            $suggestions[] = "No wrapper step — output may not be self-executing";
        }

        return [
            'valid' => empty($warnings),
            'warnings' => $warnings,
            'suggestions' => $suggestions,
        ];
    }
}
