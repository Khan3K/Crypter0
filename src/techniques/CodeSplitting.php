<?php
class CodeSplitting {
    public static function process($code, $lang, $technique = 'basic', $options = []) {
        $chunksMap = ['basic' => 3, 'heavy' => 5, 'extreme' => 8];
        $chunks = $chunksMap[$technique] ?? ($options['chunks'] ?? 3);
        $lines = explode("\n", $code);
        $chunkSize = max(1, intval(count($lines) / $chunks));
        $parts = array_chunk($lines, $chunkSize);
        $encoded = [];
        foreach ($parts as $i => $part) {
            $chunkCode = implode("\n", $part);
            switch ($lang) {
                case 'python':
                    $encoded[] = 'exec(__import__("base64").b64decode(' . var_export(base64_encode($chunkCode), true) . ').decode())';
                    break;
                case 'javascript':
                    $encoded[] = 'eval((typeof atob!=="undefined"?atob:function(b){return Buffer.from(b,"base64").toString()})(' . json_encode(base64_encode($chunkCode)) . '))';
                    break;
                default:
                    $encoded[] = var_export($chunkCode, true);
                    break;
            }
        }
        if ($lang === 'python') {
            return implode("\n", array_map(function($e) { return $e; }, $encoded));
        } elseif ($lang === 'javascript') {
            return implode("\n", array_map(function($e) { return $e . ';'; }, $encoded));
        }
        return $code;
    }
}
