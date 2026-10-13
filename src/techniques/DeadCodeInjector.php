<?php
class DeadCodeInjector {
    public static function inject($code, $lang, $mode = 'medium', $options = []) {
        $density = $options['density'] ?? $mode;
        switch ($density) {
            case 'light': return self::lightInject($code, $lang);
            case 'heavy': return self::heavyInject($code, $lang);
            case 'extreme': return self::extremeInject($code, $lang);
            case 'medium': default: return self::mediumInject($code, $lang);
        }
    }

    private static function lightInject($code, $lang) {
        $dead = self::getGenerators($lang);
        $injected = [];
        for ($i = 0; $i < 2 && $i < count($dead); $i++) {
            $injected[] = $dead[$i]();
        }
        return self::insert($code, $injected, $lang);
    }

    private static function mediumInject($code, $lang) {
        $dead = self::getGenerators($lang);
        $injected = [];
        $count = min(3 + random_int(0, 2), count($dead));
        for ($i = 0; $i < $count; $i++) {
            $injected[] = $dead[array_rand($dead)]();
        }
        return self::insert($code, $injected, $lang);
    }

    private static function heavyInject($code, $lang) {
        $dead = self::getGenerators($lang);
        $injected = [];
        $count = min(5 + random_int(0, 5), count($dead));
        for ($i = 0; $i < $count; $i++) {
            $injected[] = $dead[array_rand($dead)]();
        }
        return self::insert($code, $injected, $lang);
    }

    private static function extremeInject($code, $lang) {
        $dead = self::getGenerators($lang);
        $injected = [];
        $count = min(10 + random_int(0, 10), count($dead) * 3);
        for ($i = 0; $i < $count; $i++) {
            $injected[] = $dead[array_rand($dead)]();
        }
        return self::insert($code, $injected, $lang);
    }

    private static function insert($code, $lines, $lang) {
        if (empty($lines)) return $code;
        $codeLines = explode("\n", $code);
        $codeCount = count($codeLines);
        if ($codeCount <= 1) {
            return $code . "\n" . implode("\n", $lines);
        }
        $usable = max(1, intval($codeCount / 4));
        $usable = min($usable, count($lines));
        $positions = [];
        $taken = [0, $codeCount - 1];
        for ($i = 0; $i < $usable; $i++) {
            $pos = random_int(1, $codeCount - 2);
            $attempts = 0;
            while (in_array($pos, $taken) && $attempts < 20) {
                $pos = random_int(1, $codeCount - 2);
                $attempts++;
            }
            $positions[] = $pos;
            $taken[] = $pos;
        }
        sort($positions);
        $injectedLines = [];
        $offset = 0;
        foreach ($positions as $idx => $pos) {
            $adjustedPos = $pos + $offset;
            if ($idx < count($lines)) {
                array_splice($codeLines, $adjustedPos, 0, [$lines[$idx]]);
                $offset++;
            }
        }
        $remaining = array_slice($lines, count($positions));
        if (!empty($remaining)) {
            foreach ($remaining as $line) {
                $pos = random_int(1, count($codeLines) - 1);
                array_splice($codeLines, $pos, 0, [$line]);
            }
        }
        return implode("\n", $codeLines);
    }

    private static function getGenerators($lang) {
        $var1 = RandomProvider::string(6);
        $var2 = RandomProvider::string(6);
        $var3 = RandomProvider::string(6);
        $func = RandomProvider::string(8);
        $val1 = random_int(1, 9999);
        $val2 = random_int(1, 9999);
        $val3 = random_int(1, 9999);

        $generators = [];

        switch ($lang) {
            case 'python':
                $generators = [
                    function() use ($var1, $val1) { return "_{$var1} = {$val1}"; },
                    function() use ($var1, $var2, $val1) { return "_{$var1} = lambda _{$var2}: {$val1} + _{$var2}"; },
                    function() use ($var1, $var2, $var3) { return "if _{$var1} if False else None: _{$var2} = _{$var3}"; },
                    function() use ($var1, $val1, $val2) { return "_{$var1} = [{$val1}, {$val2}, {$val1} + {$val2}]"; },
                    function() use ($var1, $var2) { return "_{$var2} = type('_{$var1}', (), {})()"; },
                    function() use ($var1, $val1) { return "_{$var1} = next((x for x in range({$val1}) if False), None)"; },
                    function() use ($var1, $var2, $val1) { return "class _{$var1}:\n    _{$var2} = {$val1}"; },
                    function() use ($var1, $var2, $val1, $val2) { return "_{$var1} = {k: v for k, v in zip([{$val1}], [{$val2}]) if False}"; },
                ];
                break;
            case 'javascript': case 'typescript':
                $generators = [
                    function() use ($var1, $val1) { return "var _{$var1} = {$val1};"; },
                    function() use ($var1, $var2, $val1) { return "var _{$var1} = function(_{$var2}) { return {$val1} + _{$var2}; };"; },
                    function() use ($var1, $var2) { return "var _{$var1} = _{$var2} || undefined;"; },
                    function() use ($var1, $val1, $val2) { return "var _{$var1} = [{$val1}, {$val2}].reduce(function(a, b) { return a + b; }, 0);"; },
                    function() use ($var1, $var2, $var3) { return "try { _{$var1} = _{$var2}; } catch(e) { _{$var3} = e; }"; },
                    function() use ($var1, $var2, $val1) { return "_{$var1}: (function() { var _{$var2} = {$val1}; return _{$var2}; })();"; },
                ];
                break;
            case 'php':
                $generators = [
                    function() use ($var1, $val1) { return "\$_{$var1} = {$val1};"; },
                    function() use ($var1, $var2, $val1) { return "\$_{$var1} = function(\$_{$var2}) { return {$val1} + \$_{$var2}; };"; },
                    function() use ($var1) { return "if (false) { \$_{$var1} = null; }"; },
                    function() use ($var1, $var2) { return "\$_{$var1} = @array_key_exists('x', []); \$_{$var2} = false;"; },
                    function() use ($var1, $val1) { return "\$_{$var1} = range(1, {$val1}); \$_{$var1} = array_reverse(\$_{$var1});"; },
                ];
                break;
            case 'ruby':
                $generators = [
                    function() use ($var1, $val1) { return "_{$var1} = {$val1}"; },
                    function() use ($var1, $var2, $val1) { return "_{$var1} = ->(_{$var2}) { {$val1} + _{$var2} }"; },
                    function() use ($var1) { return "_{$var1} = defined?(_{$var1}) ? _{$var1} : nil"; },
                ];
                break;
            case 'go':
                $generators = [
                    function() use ($var1, $val1) { return "_ = {$val1}"; },
                    function() use ($var1, $var2) { return "_ = [2]interface{}{nil, nil}"; },
                    function() use ($var1, $val1) { return "if false { _ = {$val1} }"; },
                    function() use ($func, $val1) { return "_ = func(_ int) int { return {$val1} }(0)"; },
                    function() use ($var1, $val1) { return "_{$var1} := []int{{$val1}}; _ = _{$var1}"; },
                    function() use ($func) { return "_ = func() { _ = nil }()"; },
                ];
                break;
            case 'csharp':
                $generators = [
                    function() use ($var1, $val1) { return "var _{$var1} = {$val1};"; },
                    function() use ($var1, $val1) { return "if (false) { var _{$var1} = {$val1}; }"; },
                    function() use ($var1) { return "var _{$var1} = new object();"; },
                    function() use ($var1, $val1) { return "var _{$var1} = new Func<int,int>(a => a + {$val1})(42);"; },
                    function() use ($var1, $val1) { return "int[] _{$var1} = new int[]{{1, {$val1}, 3}};"; },
                    function() use ($var1) { return "var _{$var1} = default(object);"; },
                ];
                break;
            case 'java':
                $generators = [
                    function() use ($var1, $val1) { return "int _{$var1} = {$val1};"; },
                    function() use ($var1, $val1) { return "if (false) { int _{$var1} = {$val1}; }"; },
                    function() use ($var1) { return "Object _{$var1} = new Object();"; },
                    function() use ($var1, $val1) { return "int[] _{$var1} = new int[]{1, {$val1}, 3};"; },
                    function() use ($var1) { return "String _{$var1} = null;"; },
                    function() use ($var1, $val1) { return "var _{$var1} = java.util.Arrays.asList(1, {$val1});"; },
                ];
                break;
            case 'kotlin':
                $generators = [
                    function() use ($var1, $val1) { return "val _{$var1} = {$val1}"; },
                    function() use ($var1, $val1) { return "if (false) { val _{$var1} = {$val1} }"; },
                    function() use ($var1) { return "val _{$var1} = Any()"; },
                    function() use ($var1, $val1) { return "val _{$var1} = (a: Int) -> a + {$val1}"; },
                    function() use ($var1) { return "val _{$var1} = listOf(1, 2, 3)"; },
                    function() use ($var1, $val1) { return "val _{$var1} = mapOf(\"a\" to {$val1})"; },
                ];
                break;
            case 'scala':
                $generators = [
                    function() use ($var1, $val1) { return "val _{$var1} = {$val1}"; },
                    function() use ($var1, $val1) { return "if (false) { val _{$var1} = {$val1} }"; },
                    function() use ($var1) { return "val _{$var1} = new AnyRef"; },
                    function() use ($var1, $val1) { return "val _{$var1} = (a: Int) => a + {$val1}"; },
                    function() use ($var1) { return "val _{$var1} = List(1, 2, 3)"; },
                    function() use ($var1, $val1) { return "val _{$var1} = Map(\"a\" -> {$val1})"; },
                ];
                break;
            case 'perl':
                $generators = [
                    function() use ($var1, $val1) { return "my \$${var1} = {$val1};"; },
                    function() use ($var1, $val1) { return "if (0) { my \$${var1} = {$val1}; }"; },
                    function() use ($var1) { return "my \$${var1} = sub { return undef; };"; },
                    function() use ($var1, $val1) { return "my @${var1} = (1, {$val1}, 3);"; },
                ];
                break;
            case 'lua':
                $generators = [
                    function() use ($var1, $val1) { return "local _{$var1} = {$val1}"; },
                    function() use ($var1, $var2, $val1) { return "local function _{$func}(_{$var2}) return _{$var2} + {$val1} end"; },
                    function() use ($var1) { return "if false then local _{$var1} = 0 end"; },
                    function() use ($var1) { return "local _{$var1} = {}"; },
                ];
                break;
            case 'bash':
                $generators = [
                    function() use ($var1, $val1) { return "_{$var1}={$val1}"; },
                    function() use ($var1, $val1) { return "if false; then _{$var1}={$val1}; fi"; },
                    function() use ($var1) { return "_{$var1}=(1 2 3)"; },
                ];
                break;
            default:
                $generators = [
                    function() use ($var1, $val1) { return "// dead code: _{$var1} = {$val1}"; },
                    function() use ($var1) { return "/* unreachable: _{$var1} */"; },
                ];
        }
        return $generators;
    }
}
