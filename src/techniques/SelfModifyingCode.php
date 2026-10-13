<?php
class SelfModifyingCode {
    private static $supportedLangs = ['python', 'javascript', 'typescript', 'php'];

    public static function process($code, $lang, $mode = 'basic', $options = []) {
        if (!in_array($lang, self::$supportedLangs)) return $code;

        $method = $options['method'] ?? $mode;
        switch ($method) {
            case 'basic': return self::basicSelfMod($code, $lang);
            case 'runtime': return self::runtimeDecompile($code, $lang);
            case 'mutation': return self::mutationCode($code, $lang);
            case 'chameleon': return self::chameleonCode($code, $lang);
            default: return $code;
        }
    }

    private static function basicSelfMod($code, $lang) {
        $b64 = base64_encode($code);
        $key = RandomProvider::hexString(16);
        $enc = '';
        for ($i = 0; $i < strlen($code); $i++) {
            $enc .= chr(ord($code[$i]) ^ ord($key[$i % 16]));
        }
        $encB64 = base64_encode($enc);

        switch ($lang) {
            case 'python':
                return <<<PY
import base64 as B,os as O,time as T
_k='$key'
_c=B.b64decode('$encB64')
_d=bytes([a^b for a,b in zip(_c,_k.encode()*len(_c))])
exec(_d.decode())
PY;
            case 'javascript': case 'typescript':
                return <<<JS
(function(){
var k='$key',c=(typeof atob!=='undefined'?atob:function(b){return Buffer.from(b,'base64').toString()})('$encB64'),d='';
for(var i=0;i<c.length;i++)d+=String.fromCharCode(c.charCodeAt(i)^k.charCodeAt(i%k.length));
eval(d);
})();
JS;
            case 'php':
                return "<?php \$k='$key';\$c=base64_decode('$encB64');\$d='';for(\$i=0;\$i<strlen(\$c);\$i++)\$d.=chr(ord(\$c[\$i])^ord(\$k[\$i%16]));eval(\$d);";
            default:
                return base64_decode($b64);
        }
    }

    private static function runtimeDecompile($code, $lang) {
        $b64 = base64_encode($code);
        $chunks = str_split($b64, 8);
        $shuffled = $chunks;
        $order = range(0, count($shuffled) - 1);
        shuffle($order);
        $key = implode('', $order);

        switch ($lang) {
            case 'python':
                $orderJson = json_encode($order);
                $chunkStr = implode("','", $chunks);
                return <<<PY
import base64 as B,json as J
_order=$orderJson
_chunks=['{$chunkStr}']
_recovered=['']*len(_order)
for _i,_o in enumerate(_order):_recovered[_o]=_chunks[_i]
exec(B.b64decode(''.join(_recovered)).decode())
PY;
            default:
                return base64_decode($b64);
        }
    }

    private static function mutationCode($code, $lang) {
        $b64 = base64_encode($code);
        $key = RandomProvider::hexString(16);
        $enc = '';
        for ($i = 0; $i < strlen($code); $i++) {
            $enc .= chr(ord($code[$i]) ^ ord($key[$i % 16]));
        }
        $encB64 = base64_encode($enc);
        $seed = random_int(0, PHP_INT_MAX);

        switch ($lang) {
            case 'python':
                return <<<PY
import base64 as B,random as R,os as O
R.seed($seed)
_k='$key'
_c=B.b64decode('$encB64')
_j=R.sample(range(len(_c)),len(_c))
_r=[0]*len(_c)
for _i,_j_i in enumerate(_j):_r[_j_i]=_c[_i]
_d=bytes([a^b for a,b in zip(bytes(_r),_k.encode()*(len(_r)//16+1))])[:len(_r)]
exec(_d.decode())
PY;
            default:
                return self::basicSelfMod($code, $lang);
        }
    }

    private static function chameleonCode($code, $lang) {
        $b64 = base64_encode($code);
        switch ($lang) {
            case 'python':
                $contexts = [
                    'if True:',
                    'try:',
                    'with open("/dev/null") as _:',
                    'for _i in range(1):',
                    'while False: pass',
                ];
                $wrapper = $contexts[array_rand($contexts)];
                $indent = '    ';
                $lines = explode("\n", $code);
                $indented = $wrapper . "\n";
                foreach ($lines as $line) $indented .= $indent . $line . "\n";
                return $indented;
            case 'javascript': case 'typescript':
                $contexts = [
                    '!function(){',
                    'try{',
                    'if(true){',
                    '!function main(){',
                    'void function(){',
                ];
                $wrapper = $contexts[array_rand($contexts)];
                return $wrapper . "\n" . $code . "\n}();";
            case 'php':
                $contexts = [
                    '<?php if(true){',
                    '<?php try{',
                    '<?php for($i=0;$i<1;$i++){',
                ];
                $wrapper = $contexts[array_rand($contexts)];
                return $wrapper . "\n" . $code . "\n}?>";
            case 'go':
                $contexts = [
                    '_ = func() {',
                    'func() {',
                ];
                $wrapper = $contexts[array_rand($contexts)];
                return $wrapper . "\n" . $code . "\n}()";
            case 'java':
                $contexts = [
                    'if (true) {',
                    'try {',
                ];
                $wrapper = $contexts[array_rand($contexts)];
                $lines = explode("\n", $code);
                $indented = $wrapper . "\n";
                foreach ($lines as $line) $indented .= "    " . $line . "\n";
                $indented .= "}";
                return $indented;
            case 'csharp':
                $contexts = [
                    'if (true) {',
                    'try {',
                    'do {',
                ];
                $wrapper = $contexts[array_rand($contexts)];
                $lines = explode("\n", $code);
                $indented = $wrapper . "\n";
                foreach ($lines as $line) $indented .= "    " . $line . "\n";
                $indented .= "}";
                return $indented;
            case 'kotlin':
                $contexts = [
                    'if (true) {',
                    'run {',
                    'also {',
                ];
                $wrapper = $contexts[array_rand($contexts)];
                $lines = explode("\n", $code);
                $indented = $wrapper . "\n";
                foreach ($lines as $line) $indented .= "    " . $line . "\n";
                $indented .= "}";
                return $indented;
            case 'scala':
                $contexts = [
                    'if (true) {',
                    'Option({',
                    '{',
                ];
                $wrapper = $contexts[array_rand($contexts)];
                $lines = explode("\n", $code);
                $indented = $wrapper . "\n";
                foreach ($lines as $line) $indented .= "    " . $line . "\n";
                $indented .= "}";
                return $indented;
            case 'ruby':
                $contexts = [
                    'if true then',
                    'begin',
                    '1.times do',
                ];
                $wrapper = $contexts[array_rand($contexts)];
                $indent = '  ';
                $lines = explode("\n", $code);
                $indented = $wrapper . "\n";
                foreach ($lines as $line) $indented .= $indent . $line . "\n";
                $indented .= 'end';
                return $indented;
            case 'lua':
                $contexts = [
                    'do',
                    'if true then',
                ];
                $wrapper = $contexts[array_rand($contexts)];
                $indent = '  ';
                $lines = explode("\n", $code);
                $indented = $wrapper . "\n";
                foreach ($lines as $line) $indented .= $indent . $line . "\n";
                $indented .= 'end';
                return $indented;
            case 'bash':
                $contexts = [
                    'if true; then',
                    '{',
                ];
                $wrapper = $contexts[array_rand($contexts)];
                $indent = '  ';
                $lines = explode("\n", $code);
                $indented = $wrapper . "\n";
                foreach ($lines as $line) $indented .= $indent . $line . "\n";
                $indented .= '}';
                return $indented;
            default:
                return $code;
        }
    }
}
