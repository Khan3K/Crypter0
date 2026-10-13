<?php
class CodeVirtualizer {
    private static $supportedLangs = ['python', 'javascript', 'typescript', 'php', 'ruby', 'perl', 'lua'];

    public static function process($code, $lang, $mode = 'basic', $options = []) {
        if (!in_array($lang, self::$supportedLangs)) return $code;

        switch ($mode) {
            case 'basic': return self::basicVirtualize($code, $lang, $options);
            case 'bytecode': return self::bytecodeEncode($code, $lang, $options);
            case 'stack_vm': return self::stackVMEncode($code, $lang, $options);
            case 'register_vm': return self::registerVMEncode($code, $lang, $options);
            default: return $code;
        }
    }

    private static function basicVirtualize($code, $lang, $options = []) {
        $keySize = $options['key_size'] ?? 32;
        $key = RandomProvider::hexString($keySize);
        $xored = '';
        for ($i = 0; $i < strlen($code); $i++) {
            $xored .= chr(ord($code[$i]) ^ ord($key[$i % strlen($key)]));
        }
        $encoded = base64_encode($xored);
        switch ($lang) {
            case 'python': return self::pythonVM($encoded, $key);
            case 'javascript': case 'typescript': return self::jsVM($encoded, $key);
            case 'php': return self::phpVM($encoded, $key);
            default: return self::genericVM($encoded, $key, $lang);
        }
    }

    private static function pythonVM($bytecode, $key) {
        $b = var_export($bytecode, true);
        $k = var_export($key, true);
        $xb = self::pyVar('b');
        $xk = self::pyVar('k');
        $xd = self::pyVar('d');
        $xn = self::pyVar('n');
        $xr = self::pyVar('r');
        return "import base64 as _bx\n"
             . "{$xb}={$b}\n"
             . "{$xk}={$k}.encode()\n"
             . "{$xd}=_bx.b64decode({$xb})\n"
             . "{$xn}=len({$xd})\n"
             . "{$xr}=bytes([a^b for a,b in zip({$xd},{$xk}*({$xn}//len({$xk})+1))])[:{$xn}]\n"
             . "exec({$xr}.decode())\n";
    }

    private static function pyVar($name) { return '_x_' . $name . '_'; }

    private static function jsVM($bytecode, $key) {
        return "var b='" . $bytecode . "',h='" . $key . "';"
             . "var d=atob(b),k=h,r=[];"
             . "for(var i=0;i<d.length;i++)r.push(String.fromCharCode(d.charCodeAt(i)^k.charCodeAt(i%k.length)));"
             . "eval(r.join(''));";
    }

    private static function phpVM($bytecode, $key) {
        $b = var_export($bytecode, true);
        $k = var_export($key, true);
        return "<?php \$b={$b};\$k={$k};\$d=base64_decode(\$b);\$r='';"
             . "for(\$i=0;\$i<strlen(\$d);\$i++)"
             . "\$r.=chr(ord(\$d[\$i])^ord(\$k[\$i%strlen(\$k)]));"
             . "eval(\$r);";
    }

    private static function genericVM($bytecode, $key, $lang) {
        switch ($lang) {
            case 'ruby':
                return "eval(Base64.decode64('" . $bytecode . "').bytes.zip('" . $key . "'.bytes.cycle.take(999)).map{|a,b| a^b}.pack('C*'))";
            case 'perl':
                return "use MIME::Base64;my \$d=decode_base64('" . $bytecode . "');my \$k='" . $key . "';"
                     . "my \$r='';for(0..length(\$d)-1){\$r.=chr(ord(substr(\$d,\$_,1))^ord(substr(\$k,\$_%length(\$k),1)))}eval(\$r);";
            case 'lua':
                return "local d=require('base64').decode('" . $bytecode . "')"
                     . "local k='" . $key . "'"
                     . "local r=''"
                     . "for i=1,#d do r=r..string.char(bit.bxor(string.byte(d,i),string.byte(k,((i-1)%#k)+1))) end"
                     . " load(r)()";
            default:
                return base64_decode($bytecode);
        }
    }

    private static function bytecodeEncode($code, $lang, $options = []) {
        $ops = [];
        $lines = explode("\n", $code);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) continue;
            $ops[] = [random_int(0x10, 0xFF), $trimmed];
        }
        $jsonData = json_encode($ops);
        $keySize = $options['key_size'] ?? 16;
        $vmKey = RandomProvider::hexString($keySize);
        $xored = '';
        for ($i = 0; $i < strlen($jsonData); $i++) {
            $xored .= chr(ord($jsonData[$i]) ^ ord($vmKey[$i % 16]));
        }
        $encData = base64_encode($xored);
        switch ($lang) {
            case 'python':
                $b = var_export($encData, true);
                $k = var_export($vmKey, true);
                $xb = self::pyVar('b');
                $xk = self::pyVar('k');
                $xd = self::pyVar('d');
                $xr = self::pyVar('r');
                return "import base64 as _bx\n"
                     . "{$xb}={$b}\n"
                     . "{$xk}={$k}.encode()\n"
                     . "{$xd}=_bx.b64decode({$xb})\n"
                     . "{$xr}=bytes([a^b for a,b in zip({$xd},{$xk}*({$xd}.__len__()//{$xk}.__len__()+1))])[:{$xd}.__len__()]\n"
                     . "import json as _j\n"
                     . "for _op,_ln in _j.loads({$xr}.decode()): exec(_ln)\n";
            case 'javascript': case 'typescript':
                return "var b='" . $encData . "',k='" . $vmKey . "';"
                     . "var d=atob(b),r='';"
                     . "for(var i=0;i<d.length;i++)r+=String.fromCharCode(d.charCodeAt(i)^k.charCodeAt(i%k.length));"
                     . "var ops=JSON.parse(r);"
                     . "for(var j=0;j<ops.length;j++){eval(ops[j][1]);}";
            case 'php':
                $b = var_export($encData, true);
                $k = var_export($vmKey, true);
                return "<?php \$b={$b};\$k={$k};\$d=base64_decode(\$b);\$r='';"
                     . "for(\$i=0;\$i<strlen(\$d);\$i++)\$r.=chr(ord(\$d[\$i])^ord(\$k[\$i%strlen(\$k)]));"
                     . "\$ops=json_decode(\$r,true);foreach(\$ops as \$o)eval(\$o[1]);";
            default:
                return self::basicVirtualize(json_encode($ops), $lang);
        }
    }

    private static function stackVMEncode($code, $lang, $options = []) {
        $opcodes = [];
        $lines = explode("\n", $code);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) continue;
            $opcodes[] = ['PUSH', base64_encode($trimmed)];
            $opcodes[] = ['EXEC', ''];
        }
        $jsonData = json_encode($opcodes);
        $keySize = $options['key_size'] ?? 16;
        $vmKey = RandomProvider::hexString($keySize);
        $xored = '';
        for ($i = 0; $i < strlen($jsonData); $i++) {
            $xored .= chr(ord($jsonData[$i]) ^ ord($vmKey[$i % 16]));
        }
        $encData = base64_encode($xored);
        switch ($lang) {
            case 'python':
                $b = var_export($encData, true);
                $k = var_export($vmKey, true);
                $xb = self::pyVar('b');
                $xk = self::pyVar('k');
                $xd = self::pyVar('d');
                $xr = self::pyVar('r');
                return "import base64 as _bx\n"
                     . "{$xb}={$b}\n"
                     . "{$xk}={$k}.encode()\n"
                     . "{$xd}=_bx.b64decode({$xb})\n"
                     . "{$xr}=bytes([a^b for a,b in zip({$xd},{$xk}*({$xd}.__len__()//{$xk}.__len__()+1))])[:{$xd}.__len__()]\n"
                     . "import json as _j\n"
                     . "_st=[]\n"
                     . "for _op,_arg in _j.loads({$xr}.decode()):\n"
                     . "    if _op=='PUSH': _st.append(_bx.b64decode(_arg).decode())\n"
                     . "    elif _op=='EXEC' and _st: exec(_st.pop())\n";
            case 'javascript': case 'typescript':
                return "var b='" . $encData . "',k='" . $vmKey . "';"
                     . "var d=atob(b),r='';"
                     . "for(var i=0;i<d.length;i++)r+=String.fromCharCode(d.charCodeAt(i)^k.charCodeAt(i%k.length));"
                     . "var ops=JSON.parse(r),st=[];"
                     . "for(var j=0;j<ops.length;j++){"
                     . "if(ops[j][0]==='PUSH')st.push(atob(ops[j][1]));"
                     . "else if(ops[j][0]==='EXEC'&&st.length)eval(st.pop());}";
            case 'php':
                $b = var_export($encData, true);
                $k = var_export($vmKey, true);
                return "<?php \$b={$b};\$k={$k};\$d=base64_decode(\$b);\$r='';"
                     . "for(\$i=0;\$i<strlen(\$d);\$i++)\$r.=chr(ord(\$d[\$i])^ord(\$k[\$i%strlen(\$k)]));"
                     . "\$ops=json_decode(\$r,true);\$st=[];"
                     . "foreach(\$ops as \$o){if(\$o[0]==='PUSH')\$st[]=base64_decode(\$o[1]);"
                     . "elseif(\$o[0]==='EXEC'&&\$st)eval(array_pop(\$st));}";
            default:
                return self::basicVirtualize(json_encode($opcodes), $lang);
        }
    }

    private static function registerVMEncode($code, $lang, $options = []) {
        $regs = [];
        $lines = explode("\n", $code);
        $regNames = ['R0', 'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'R7'];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) continue;
            $reg = $regNames[array_key_exists(0, $regs) ? count($regs) % count($regNames) : 0];
            $regs[] = ['SET', $reg, base64_encode($trimmed)];
        }
        $regs[] = ['EXEC_ALL', ''];
        $jsonData = json_encode($regs);
        $keySize = $options['key_size'] ?? 16;
        $vmKey = RandomProvider::hexString($keySize);
        $xored = '';
        for ($i = 0; $i < strlen($jsonData); $i++) {
            $xored .= chr(ord($jsonData[$i]) ^ ord($vmKey[$i % 16]));
        }
        $encData = base64_encode($xored);
        switch ($lang) {
            case 'python':
                $b = var_export($encData, true);
                $k = var_export($vmKey, true);
                $xb = self::pyVar('b');
                $xk = self::pyVar('k');
                $xd = self::pyVar('d');
                $xr = self::pyVar('r');
                return "import base64 as _bx\n"
                     . "{$xb}={$b}\n"
                     . "{$xk}={$k}.encode()\n"
                     . "{$xd}=_bx.b64decode({$xb})\n"
                     . "{$xr}=bytes([a^b for a,b in zip({$xd},{$xk}*({$xd}.__len__()//{$xk}.__len__()+1))])[:{$xd}.__len__()]\n"
                     . "import json as _j\n"
                     . "_rg={}\n"
                     . "for _op in _j.loads({$xr}.decode()):\n"
                     . "    if _op[0]=='SET': _rg[_op[1]]=_bx.b64decode(_op[2]).decode()\n"
                     . "    elif _op[0]=='EXEC_ALL':\n"
                     . "        for _v in _rg.values(): exec(_v)\n";
            case 'javascript': case 'typescript':
                return "var b='" . $encData . "',k='" . $vmKey . "';"
                     . "var d=atob(b),r='';"
                     . "for(var i=0;i<d.length;i++)r+=String.fromCharCode(d.charCodeAt(i)^k.charCodeAt(i%k.length));"
                     . "var ops=JSON.parse(r),rg={};"
                     . "for(var j=0;j<ops.length;j++){"
                     . "if(ops[j][0]==='SET')rg[ops[j][1]]=atob(ops[j][2]);"
                     . "else if(ops[j][0]==='EXEC_ALL'){for(var rn in rg)eval(rg[rn]);}}";
            case 'php':
                $b = var_export($encData, true);
                $k = var_export($vmKey, true);
                return "<?php \$b={$b};\$k={$k};\$d=base64_decode(\$b);\$r='';"
                     . "for(\$i=0;\$i<strlen(\$d);\$i++)\$r.=chr(ord(\$d[\$i])^ord(\$k[\$i%strlen(\$k)]));"
                     . "\$ops=json_decode(\$r,true);\$rg=[];"
                     . "foreach(\$ops as \$o){if(\$o[0]==='SET')\$rg[\$o[1]]=base64_decode(\$o[2]);"
                     . "elseif(\$o[0]==='EXEC_ALL'){foreach(\$rg as \$v)eval(\$v);}}";
            default:
                return self::basicVirtualize(json_encode($regs), $lang);
        }
    }
}
