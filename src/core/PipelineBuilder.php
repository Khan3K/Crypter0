<?php
class PipelineBuilder {
    private $steps = [];
    private $code = '';
    private $lang = '';

    public function __construct($code, $lang) {
        $this->code = $code;
        $this->lang = $lang;
    }

    public function addStep($step) { $this->steps[] = $step; return $this; }
    public function setSteps($steps) { $this->steps = $steps; return $this; }

    public function execute() {
        $currentCode = $this->code;
        $allKeys = [];
        $appliedSteps = [];
        $steps = $this->normalizeSteps($this->steps);

        foreach ($steps as $i => $step) {
            $category = $step['category'] ?? 'string';
            $technique = $step['technique'] ?? '';
            $options = $step['options'] ?? [];
            $sizeBefore = strlen($currentCode);

            $result = $this->applyStep($category, $technique, $currentCode, $options, $allKeys);
            $currentCode = $result['code'];
            if (isset($result['keys'])) $allKeys = array_merge($allKeys, $result['keys']);

            $appliedSteps[] = [
                'step' => $i + 1, 'category' => $category, 'technique' => $technique,
                'size_before' => $sizeBefore, 'size_after' => strlen($currentCode),
            ];
        }

        return [
            'code' => $currentCode,
            'keys' => $allKeys,
            'steps' => $appliedSteps,
            'final_size' => strlen($currentCode),
            'original_size' => strlen($this->code),
        ];
    }

    private function normalizeSteps($steps) {
        $nonWrapper = [];
        $wrappers = [];
        foreach ($steps as $step) {
            $step = $this->normalizeStep($step);
            $cat = $step['category'] ?? '';
            if ($cat === 'wrapper') {
                $wrappers[] = $step;
            } else {
                $nonWrapper[] = $step;
            }
        }
        if (!empty($wrappers)) {
            $last = array_pop($wrappers);
            $nonWrapper[] = $last;
        }
        return $nonWrapper;
    }

    private function normalizeStep($step) {
        return [
            'category' => $step['category'] ?? $step['cat'] ?? '',
            'technique' => $step['technique'] ?? $step['tech'] ?? '',
            'options' => $step['options'] ?? $step['opts'] ?? [],
        ];
    }

    private function applyStep($category, $technique, $code, $options, $accumulatedKeys) {
        $sizeBefore = strlen($code);
        $keys = [];
        try {
            switch ($category) {
                case 'transform':
                    $code = VariableRenamer::process($code, $this->lang, $technique, $options);
                    break;
                case 'deadcode':
                    $code = DeadCodeInjector::inject($code, $this->lang, $technique, $options);
                    break;
                case 'controlflow':
                    $code = ControlFlowObfuscator::process($code, $this->lang, $technique, $options);
                    break;
                case 'anti':
                    $code = AntiDebug::inject($code, $this->lang, $technique, $options);
                    break;
                case 'instruct':
                    $code = InstructionSubstitution::process($code, $this->lang, $technique, $options);
                    break;
                case 'strencrypt':
                    $code = StringEncryption::process($code, $this->lang, $technique, $options);
                    break;
                case 'opaque':
                    $code = OpaquePredicates::process($code, $this->lang, $technique, $options);
                    break;
                case 'codevir':
                    $code = CodeVirtualizer::process($code, $this->lang, $technique, $options);
                    break;
                case 'selfmod':
                    $code = SelfModifyingCode::process($code, $this->lang, $technique, $options);
                    break;
                case 'antiai':
                    $code = AntiAI::process($code, $this->lang, $technique, $options);
                    break;
                case 'watermark':
                    $code = Watermarking::process($code, $this->lang, $technique, $options);
                    break;
                case 'polymorphic':
                    $code = PolymorphicEngine::process($code, $this->lang, $technique, $options);
                    break;
                case 'split':
                    $code = CodeSplitting::process($code, $this->lang, $technique, $options);
                    break;
                case 'stegano':
                    $code = Steganography::process($code, $this->lang, $technique, $options);
                    break;
                case 'codeenc':
                    $result = StringObfuscation::encode($code, $technique, $options);
                    $encoded = is_array($result) ? ($result['data'] ?? $code) : $result;
                    $code = self::wrapCodeEncoding($encoded, $technique, $this->lang);
                    break;
                case 'marshal':
                    if ($this->lang === 'python') {
                        $code = PayloadBuilder::wrap($code, 'python', 'marshal', $accumulatedKeys);
                    }
                    break;
                case 'wrapper':
                    $code = PayloadBuilder::wrap($code, $this->lang, $technique, $accumulatedKeys);
                    break;
                case 'strarray':
                    $code = StringArrayObfuscation::process($code, $this->lang, $technique, $options);
                    break;
                case 'decompose':
                    $code = StringDecomposition::process($code, $this->lang, $technique, $options);
                    break;
                case 'autoimport':
                    $code = AutoImport::process($code, $this->lang, $options);
                    break;
            }
        } catch (\Exception $e) {
            error_log("PipelineBuilder step failed: {$category}/{$technique}: " . $e->getMessage());
        }
        return ['code' => $code, 'keys' => $keys, 'size_before' => $sizeBefore];
    }

    private static function wrapCodeEncoding($encoded, $method, $lang) {
        switch ($lang) {
            case 'python':
                switch ($method) {
                    case 'base64': return 'import base64 as _e;exec(_e.b64decode("' . $encoded . '").decode())';
                    case 'hex': return 'exec(bytes.fromhex("' . ltrim($encoded, '0x') . '").decode())';
                    case 'oct': return 'exec(bytes([int(x,8) for x in "' . $encoded . '".split()]).decode())';
                    case 'bin': return 'exec(bytes([int(x,2) for x in "' . $encoded . '".split()]).decode())';
                    case 'rot13': return 'import codecs as _c;exec(_c.decode("' . $encoded . '","rot_13"))';
                    case 'rot47': return 'exec("' . $encoded . '".translate(str.maketrans("PQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~!\"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~")))';
                }
                break;
            case 'javascript': case 'typescript':
                switch ($method) {
                    case 'base64': return 'eval((typeof atob!=="undefined"?atob:function(b){return Buffer.from(b,"base64").toString()})("' . $encoded . '"))';
                    case 'hex': return 'eval("' . $encoded . '".replace(/([0-9a-f]{2})/g,(_,h)=>String.fromCharCode(parseInt(h,16))))';
                }
                break;
            case 'php':
                switch ($method) {
                    case 'base64': return '<?php eval(base64_decode("' . $encoded . '"));?>';
                    case 'hex': return '<?php eval(hex2bin("' . $encoded . '"));?>';
                }
                break;
            case 'ruby':
                switch ($method) {
                    case 'base64': return 'eval("' . $encoded . '".unpack1("m"))';
                }
                break;
            case 'bash':
                switch ($method) {
                    case 'base64': return 'echo "' . $encoded . '" | base64 -d | bash';
                    case 'hex': return 'echo "' . $encoded . '" | xxd -r -p | bash';
                }
                break;
            case 'perl':
                switch ($method) {
                    case 'base64': return 'eval MIME::Base64::decode("' . $encoded . '")';
                }
                break;
            case 'lua':
                switch ($method) {
                    case 'base64': return 'loadstring((function(b)local c=""for i=1,#b,2 do c=c..string.char(tonumber(b:sub(i,i+1),16))end return c end)("' . bin2hex(base64_decode($encoded)) . '"))()';
                }
                break;
            case 'go':
                switch ($method) {
                    case 'base64': return 'package main;import "encoding/base64";import "fmt";func main(){d,_:=base64.StdEncoding.DecodeString("' . $encoded . '");fmt.Print(string(d))}';
                    case 'hex': return 'package main;import "encoding/hex";import "fmt";func main(){d,_:=hex.DecodeString("' . $encoded . '");fmt.Print(string(d))}';
                }
                break;
            case 'csharp':
                switch ($method) {
                    case 'base64': return 'Console.Write(System.Text.Encoding.UTF8.GetString(Convert.FromBase64String("' . $encoded . '")));';
                    case 'hex': return 'Console.Write(string(System.Hexadecimal.Parse("' . $encoded . '").Select(b=>(char)b)));';
                }
                break;
            case 'java':
                switch ($method) {
                    case 'base64': return 'System.out.print(new String(java.util.Base64.getDecoder().decode("' . $encoded . '")));';
                    case 'hex': return 'System.out.print(new String(java.util.HexFormat.of().parseHex("' . $encoded . '")));';
                }
                break;
            case 'kotlin':
                switch ($method) {
                    case 'base64': return 'print(String(java.util.Base64.getDecoder().decode("' . $encoded . '")))';
                }
                break;
            case 'scala':
                switch ($method) {
                    case 'base64': return 'print(new String(java.util.Base64.getDecoder.decode("' . $encoded . '")))';
                }
                break;
        }
        return $encoded;
    }
}
