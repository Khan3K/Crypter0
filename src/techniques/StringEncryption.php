<?php
class StringEncryption {
    public static function process($code, $lang, $mode = 'per_string', $options = []) {
        $strings = self::extractStrings($code, $lang);
        if (empty($strings)) return $code;
        $layers = max(1, min(3, $options['layers'] ?? 1));
        $cipher = $options['cipher'] ?? $mode;
        $polymorphic = ($options['polymorphic'] ?? '1') === '1' || ($options['polymorphic'] ?? '1') === true;
        $customKey = $options['custom_key'] ?? null;
        $envKey = ($options['envkey'] ?? '0') === '1' || ($options['envkey'] ?? '1') === true;
        for ($round = 0; $round < $layers; $round++) {
            $strings = self::extractStrings($code, $lang);
            if (empty($strings)) break;
            foreach ($strings as $original) {
                $encrypted = self::encryptString($original, $cipher, $customKey, $envKey);
                $replacement = self::generateDecryptStub($encrypted, $lang, $cipher);
                if ($polymorphic) $replacement = self::polymorphizeStub($replacement, $lang);
                $escaped = preg_quote($original, '/');
                $code = preg_replace_callback(
                    '/("(' . $escaped . ')"|\'(' . $escaped . ')\')/s',
                    function($m) use ($replacement) { return $replacement; },
                    $code
                );
            }
        }
        return $code;
    }

    private static function extractStrings($code, $lang) {
        $strings = [];
        preg_match_all('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/s', $code, $matches);
        foreach ($matches[1] as $m) { if ($m !== '' && strlen($m) > 1) $strings[] = $m; }
        foreach ($matches[2] as $m) { if ($m !== '' && strlen($m) > 1) $strings[] = $m; }
        return array_unique($strings);
    }

    private static function encryptString($str, $mode, $customKey = null, $envKey = false) {
        switch ($mode) {
            case 'envkey':
                $envVar = $customKey ?: 'SECRET_KEY';
                $key = getenv($envVar) ?: RandomProvider::hexString(16);
                $enc = '';
                for ($i = 0; $i < strlen($str); $i++) $enc .= chr(ord($str[$i]) ^ ord($key[$i % 16]));
                return ['data' => base64_encode($enc), 'key' => null, 'method' => 'envkey', 'env_var' => $envVar];
            case 'xor':
                $key = $customKey ?: RandomProvider::hexString(16);
                $enc = '';
                for ($i = 0; $i < strlen($str); $i++) $enc .= chr(ord($str[$i]) ^ ord($key[$i % 16]));
                return ['data' => base64_encode($enc), 'key' => $key, 'method' => 'xor'];
            case 'aes':
                $result = CryptoProvider::aesEncrypt($str);
                return ['data' => $result['data'], 'key' => $result['key'], 'method' => 'aes'];
            case 'rc4':
                $result = CryptoProvider::rc4Encode($str);
                return ['data' => base64_encode($result['data']), 'key' => $result['key'], 'method' => 'rc4'];
            case 'chacha':
                $result = CryptoProvider::chachaEncode($str);
                return ['data' => $result['data'], 'key' => $result['key'], 'method' => 'chacha'];
            case 'reverse':
                return ['data' => strrev(base64_encode($str)), 'key' => null, 'method' => 'reverse'];
            case 'caesar':
                $shift = random_int(1, 25);
                $enc = '';
                for ($i = 0; $i < strlen($str); $i++) {
                    $c = ord($str[$i]);
                    if ($c >= 65 && $c <= 90) $enc .= chr(($c - 65 + $shift) % 26 + 65);
                    elseif ($c >= 97 && $c <= 122) $enc .= chr(($c - 97 + $shift) % 26 + 97);
                    else $enc .= $str[$i];
                }
                return ['data' => base64_encode($enc), 'key' => $shift, 'method' => 'caesar'];
            default:
                return ['data' => base64_encode($str), 'key' => null, 'method' => 'base64'];
        }
    }

    private static function generateDecryptStub($encrypted, $lang, $mode) {
        $data = $encrypted['data'];
        $key = $encrypted['key'] ?? ($encrypted['env_var'] ?? null);
        switch ($lang) {
            case 'python': return self::pyDecrypt($data, $key, $mode);
            case 'javascript': case 'typescript': return self::jsDecrypt($data, $key, $mode);
            case 'php': return self::phpDecrypt($data, $key, $mode);
            case 'go': return self::goDecrypt($data, $key, $mode);
            case 'csharp': return self::csDecrypt($data, $key, $mode);
            case 'java': return self::javaDecrypt($data, $key, $mode);
            case 'kotlin': return self::kotlinDecrypt($data, $key, $mode);
            case 'scala': return self::scalaDecrypt($data, $key, $mode);
            case 'ruby': return self::rubyDecrypt($data, $key, $mode);
            case 'perl': return self::perlDecrypt($data, $key, $mode);
            case 'lua': return self::luaDecrypt($data, $key, $mode);
            case 'bash': return self::bashDecrypt($data, $key, $mode);
            default: return self::genericDecrypt($data, $key, $mode);
        }
    }

    /* ─── Python exec-based helper ─── */
    private static function pyExecStub($data, $key, $execBody) {
        $escaped = str_replace('"', '\\"', $execBody);
        return '(lambda _d,_k:(lambda _ns:exec("' . $escaped . '",_ns) or _ns["__r"])({"_d":_d,"_k":_k}))(__import__("base64").b64decode("' . $data . '"),b"' . $key . '")';
    }

    /* ─── Python ─── */
    private static function pyDecrypt($data, $key, $mode) {
        switch ($mode) {
            case 'envkey':
                $envVar = $key ?: 'SECRET_KEY';
                return self::pyExecStub($data, '',
                    '_k=os.environ.get("' . $envVar . '","").encode();' .
                    '_r="".join(chr(_d[i]^_k[i%len(_k)])for i in range(len(_d)));' .
                    '__r=_r');
            case 'xor':
                return '(lambda d,k:"".join(chr(d[i]^k[i%len(k)])for i in range(len(d))))(__import__("base64").b64decode("' . $data . '"),b"' . $key . '")';
            case 'aes':
                return self::pyExecStub($data, $key,
                    'import hashlib,subprocess as _sp;' .
                    '_iv=_d[:16];_ct=_d[16:];_kh=hashlib.sha256(_k).digest();' .
                    '__r=_sp.run(["openssl","enc","-aes-256-cbc","-d","-nopad","-iv",_iv.hex(),"-K",_kh.hex()],input=_ct,stdout=_sp.PIPE).stdout;' .
                    '__r=__r[:-__r[-1]].decode()');
            case 'rc4':
                return self::pyExecStub($data, $key,
                    '_s=list(range(256));_j=0;_kl=len(_k)' . '\\n' .
                    'for _i in range(256):_j=(_j+_s[_i]+_k[_i%_kl])%256;_s[_i],_s[_j]=_s[_j],_s[_i]' . '\\n' .
                    '_i=_j=0;_r=b""' . '\\n' .
                    'for _c in _d:_i=(_i+1)%256;_j=(_j+_s[_i])%256;_s[_i],_s[_j]=_s[_j],_s[_i];_r+=bytes([_c^_s[(_s[_i]+_s[_j])%256]])' . '\\n' .
                    '__r=_r.decode()');
            case 'reverse':
                return '__import__("base64").b64decode("' . $data . '"[::-1]).decode()';
            case 'caesar':
                return "''.join(chr((ord(c)-65-(" . $key . "))%26+65)if c.isupper()else chr((ord(c)-97-(" . $key . "))%26+97)if c.islower()else c for c in __import__(\"base64\").b64decode(\"" . $data . "\").decode())";
            case 'chacha':
                return self::pyExecStub($data, $key,
                    'import hashlib,subprocess as _sp;' .
                    '_iv=_d[:16];_ct=_d[16:];_kh=hashlib.sha256(_k).digest();' .
                    '__r=_sp.run(["openssl","enc","-aes-256-ctr","-d","-nopad","-iv",_iv.hex(),"-K",_kh.hex()],input=_ct,stdout=_sp.PIPE).stdout;' .
                    '__r=__r.decode()');
            default:
                return '__import__("base64").b64decode("' . $data . '").decode()';
        }
    }

    /* ─── JavaScript ─── */
    private static function jsDecrypt($data, $key, $mode) {
        $atob = '(typeof atob!=="undefined"?atob:function(b){return typeof Buffer!=="undefined"?Buffer.from(b,"base64").toString():decodeURIComponent(escape(window.atob(b)))})';
        switch ($mode) {
            case 'envkey':
                $envVar = $key ?: 'SECRET_KEY';
                return '(function(_d,_k){var r="";for(var i=0;i<_d.length;i++)r+=String.fromCharCode(_d.charCodeAt(i)^_k.charCodeAt(i%_k.length));return r;})(' . $atob . '("' . $data . '"),typeof process!=="undefined"&&process.env&&process.env["' . $envVar . '"]||"")';
            case 'xor':
                return '(function(_d,_k){var r="";for(var i=0;i<_d.length;i++)r+=String.fromCharCode(_d.charCodeAt(i)^_k.charCodeAt(i%_k.length));return r;})(' . $atob . '("' . $data . '"),"'.$key.'")';
            case 'aes':
                return '(function(_d,_k){var _c=typeof require!=="undefined"?require("crypto"):null;if(!_c)return ' . $atob . '(_d);var _b=Buffer.from(_d,"base64");return _c.createDecipheriv("aes-256-cbc",_c.createHash("sha256").update(_k).digest(),_b.slice(0,16)).update(_b.slice(16)).final().toString()})("' . $data . '","'.$key.'")';
            case 'rc4':
                return '(function(_d,_k){var _s=[],_j=0,_r="";for(var _i=0;_i<256;_i++)_s[_i]=_i;for(_i=0;_i<256;_i++){_j=(_j+_s[_i]+_k.charCodeAt(_i%_k.length))%256;var _t=_s[_i];_s[_i]=_s[_j];_s[_j]=_t}_i=_j=0;for(var _n=0;_n<_d.length;_n++){_i=(_i+1)%256;_j=(_j+_s[_i])%256;_t=_s[_i];_s[_i]=_s[_j];_s[_j]=_t;_r+=String.fromCharCode(_d.charCodeAt(_n)^_s[(_s[_i]+_s[_j])%256])}return _r})(' . $atob . '("' . $data . '"),"'.$key.'")';
            case 'reverse':
                return $atob . '("' . $data . '".split("").reverse().join(""))';
            case 'caesar':
                return '(function(_d,_k){return _d.split("").map(function(_c){var _o=_c.charCodeAt(0);if(_o>=65&&_o<=90)return String.fromCharCode((_o-65-_k)%26+65);if(_o>=97&&_o<=122)return String.fromCharCode((_o-97-_k)%26+97);return _c}).join("")})(' . $atob . '("' . $data . '"),' . $key . ')';
            case 'chacha':
                return '(function(_d,_k){var _c=typeof require!=="undefined"?require("crypto"):null;if(!_c)return ' . $atob . '(_d);var _b=Buffer.from(_d,"base64");return _c.createDecipheriv("aes-256-ctr",_c.createHash("sha256").update(_k).digest(),_b.slice(0,16)).update(_b.slice(16)).final().toString()})("' . $data . '","'.$key.'")';
            default:
                return $atob . '("' . $data . '")';
        }
    }

    /* ─── PHP ─── */
    private static function phpDecrypt($data, $key, $mode) {
        switch ($mode) {
            case 'envkey':
                $envVar = $key ?: 'SECRET_KEY';
                return '(function(){$d=base64_decode("' . $data . '");$k=getenv("' . $envVar . '");$r="";for($i=0;$i<strlen($d);$i++)$r.=chr(ord($d[$i])^ord($k[$i%strlen($k)]));return $r;})()';
            case 'xor':
                return '(function(){$d=base64_decode("' . $data . '");$k="' . $key . '";$r="";for($i=0;$i<strlen($d);$i++)$r.=chr(ord($d[$i])^ord($k[$i%strlen($k)]));return $r;})()';
            case 'aes':
                return '(function(){$d=base64_decode("' . $data . '");$iv=substr($d,0,16);$ct=substr($d,16);return openssl_decrypt($ct,"aes-256-cbc",hash("sha256","' . $key . '",true),OPENSSL_RAW_DATA,$iv);})()';
            case 'rc4':
                return '(function(){$d=base64_decode("' . $data . '");$k="' . $key . '";$s=range(0,255);$j=0;for($i=0;$i<256;$i++){$j=($j+$s[$i]+ord($k[$i%strlen($k)]))%256;$t=$s[$i];$s[$i]=$s[$j];$s[$j]=$t}$r="";$i=0;$j=0;for($n=0;$n<strlen($d);$n++){$i=($i+1)%256;$j=($j+$s[$i])%256;$t=$s[$i];$s[$i]=$s[$j];$s[$j]=$t;$r.=chr(ord($d[$n])^$s[($s[$i]+$s[$j])%256])}return $r;})()';
            case 'reverse':
                return 'strrev(base64_decode("' . $data . '"))';
            case 'caesar':
                return '(function(){$d=base64_decode("' . $data . '");$s=' . $key . ';$r="";for($i=0;$i<strlen($d);$i++){$c=ord($d[$i]);if($c>=65&&$c<=90)$r.=chr(($c-65-$s+26*255)%26+65);elseif($c>=97&&$c<=122)$r.=chr(($c-97-$s+26*255)%26+97);else $r.=$d[$i]}return $r;})()';
            case 'chacha':
                return '(function(){$d=base64_decode("' . $data . '");$iv=substr($d,0,16);$ct=substr($d,16);return openssl_decrypt($ct,"aes-256-ctr",hash("sha256","' . $key . '",true),OPENSSL_RAW_DATA,$iv);})()';
            default:
                return 'base64_decode("' . $data . '")';
        }
    }

    /* ─── Go ─── */
    private static function goDecrypt($data, $key, $mode) {
        $b64Key = base64_encode($key);
        switch ($mode) {
            case 'xor':
                return '(func() string { _d,_:=base64.StdEncoding.DecodeString("' . $data . '"); _k,_:=base64.StdEncoding.DecodeString("' . $b64Key . '"); for _i:=range _d { _d[_i] = _d[_i] ^ _k[_i%len(_k)] }; return string(_d) }())';
            case 'reverse':
                return '(func() string { _d,_:=base64.StdEncoding.DecodeString("' . $data . '"); for _i,_j:=0,len(_d)-1; _i<_j; _i,_j=_i+1,_j-1 { _d[_i],_d[_j]=_d[_j],_d[_i] }; return string(_d) }())';
            case 'caesar':
                return '(func() string { _d,_:=base64.StdEncoding.DecodeString("' . $data . '"); _s:=' . $key . '; _r:=make([]byte,len(_d)); for _i:=0;_i<len(_d);_i++ { _c:=_d[_i]; if _c>=65&&_c<=90 { _r[_i]=byte((_c-65-_s+26*255)%26+65) } else if _c>=97&&_c<=122 { _r[_i]=byte((_c-97-_s+26*255)%26+97) } else { _r[_i]=_c } }; return string(_r) }())';
            default:
                return '(func() string { _d,_:=base64.StdEncoding.DecodeString("' . $data . '"); return string(_d) }())';
        }
    }

    /* ─── C# ─── */
    private static function csDecrypt($data, $key, $mode) {
        switch ($mode) {
            case 'xor':
                return 'new string(Convert.FromBase64String("' . $data . '").Select((_b,_i) => (byte)(_b ^ Encoding.UTF8.GetBytes("' . $key . '")[_i % ' . strlen($key) . '])).ToArray(), 0, Convert.FromBase64String("' . $data . '").Length, Encoding.UTF8)';
            case 'reverse':
                return 'new string(Convert.FromBase64String("' . $data . '").Reverse().Select(_b => (char)_b).ToArray())';
            default:
                return 'Encoding.UTF8.GetString(Convert.FromBase64String("' . $data . '"))';
        }
    }

    /* ─── Java ─── */
    private static function javaDecrypt($data, $key, $mode) {
        switch ($mode) {
            case 'xor':
                $kLen = strlen($key);
                return 'new String(java.util.stream.IntStream.range(0,java.util.Base64.getDecoder().decode("' . $data . '").length).map(_i -> java.util.Base64.getDecoder().decode("' . $data . '")[_i] ^ "' . $key . '".charAt(_i % ' . $kLen . ')).mapToObj(_v -> (byte)_v).collect(java.util.stream.Collectors.toList()).stream().mapToInt(Byte::intValue).toArray(), java.nio.charset.StandardCharsets.UTF_8)';
            case 'reverse':
                return 'new String(java.util.Base64.getDecoder().decode("' . $data . '"), java.nio.charset.StandardCharsets.UTF_8)';
            default:
                return 'new String(java.util.Base64.getDecoder().decode("' . $data . '"), java.nio.charset.StandardCharsets.UTF_8)';
        }
    }

    /* ─── Kotlin ─── */
    private static function kotlinDecrypt($data, $key, $mode) {
        switch ($mode) {
            case 'xor':
                return 'String(java.util.Base64.getDecoder().decode("' . $data . '").mapIndexed { _i, _b -> (_b xor "' . $key . '"[_i % ' . strlen($key) . '].code).toByte() }.toByteArray())';
            case 'reverse':
                return 'String(java.util.Base64.getDecoder().decode("' . $data . '").reversed().toByteArray())';
            default:
                return 'String(java.util.Base64.getDecoder().decode("' . $data . '"))';
        }
    }

    /* ─── Scala ─── */
    private static function scalaDecrypt($data, $key, $mode) {
        switch ($mode) {
            case 'xor':
                return 'new String(java.util.Base64.getDecoder.decode("' . $data . '").zipWithIndex.map { case (_b, _i) => (_b ^ "' . $key . '"(_i % ' . strlen($key) . ')).toByte })';
            case 'reverse':
                return 'new String(java.util.Base64.getDecoder.decode("' . $data . '").map(_.toByte).reverse)';
            default:
                return 'new String(java.util.Base64.getDecoder.decode("' . $data . '"))';
        }
    }

    /* ─── Ruby ─── */
    private static function rubyDecrypt($data, $key, $mode) {
        switch ($mode) {
            case 'xor':
                return '(0...Base64.decode64("' . $data . '").bytes.length).map { |_i| Base64.decode64("' . $data . '").bytes[_i] ^ "' . $key . '".bytes[_i % ' . strlen($key) . '] }.pack("C*")';
            case 'aes':
                return '(lambda { |d,k| c = OpenSSL::Cipher::AES256.new(:CBC); c.decrypt; c.key = OpenSSL::Digest::SHA256.digest(k); c.iv = d[0,16]; c.update(d[16..-1]) + c.final }).call(Base64.decode64("' . $data . '"), "' . $key . '")';
            case 'rc4':
                return '(lambda { |d,k| s = (0..255).to_a; j = 0; klen = k.bytesize; (0..255).each { |i| j = (j + s[i] + k.bytes[i % klen]) % 256; s[i], s[j] = s[j], s[i] }; i = 0; j = 0; d.bytes.map { |b| i = (i + 1) % 256; j = (j + s[i]) % 256; s[i], s[j] = s[j], s[i]; b ^ s[(s[i] + s[j]) % 256] }.pack("C*") }).call(Base64.decode64("' . $data . '"), "' . $key . '")';
            case 'reverse':
                return 'Base64.decode64("' . $data . '").reverse';
            case 'caesar':
                return '(lambda { |d,k| d.bytes.map { |b| if b >= 65 && b <= 90 then ((b - 65 - k) % 26 + 65).chr elsif b >= 97 && b <= 122 then ((b - 97 - k) % 26 + 97).chr else b.chr end }.join }).call(Base64.decode64("' . $data . '"), ' . $key . ')';
            case 'chacha':
                return '(lambda { |d,k| c = OpenSSL::Cipher::AES256.new(:CTR); c.decrypt; c.key = OpenSSL::Digest::SHA256.digest(k); c.iv = d[0,16]; c.update(d[16..-1]) }).call(Base64.decode64("' . $data . '"), "' . $key . '")';
            default:
                return 'Base64.decode64("' . $data . '")';
        }
    }

    /* ─── Perl ─── */
    private static function perlDecrypt($data, $key, $mode) {
        switch ($mode) {
            case 'xor':
                return 'do { my $_d=MIME::Base64::decode("' . $data . '"); my $_k="' . $key . '"; my $_r=""; for my $_i (0..length($_d)-1) { $_r.=chr(ord(substr($_d,$_i,1))^ord(substr($_k,$_i%length($_k),1))) }; $_r }';
            case 'reverse':
                return 'reverse(MIME::Base64::decode("' . $data . '"))';
            case 'caesar':
                return 'do { my $_d=MIME::Base64::decode("' . $data . '"); my $_s=' . $key . '; my $_r=""; for my $_i (0..length($_d)-1) { my $_c=ord(substr($_d,$_i,1)); if ($_c>=65 && $_c<=90) { $_r.=chr(($_c-65-$_s)%26+65) } elsif ($_c>=97 && $_c<=122) { $_r.=chr(($_c-97-$_s)%26+97) } else { $_r.=substr($_d,$_i,1) } }; $_r }';
            default:
                return 'MIME::Base64::decode("' . $data . '")';
        }
    }

    /* ─── Lua ─── */
    private static function luaDecrypt($data, $key, $mode) {
        $hexData = bin2hex(base64_decode($data));
        $hexKey = bin2hex($key);
        switch ($mode) {
            case 'xor':
                return '(function(_d,_k) local _r="" for _i=1,#_d,2 do local _db=tonumber(string.sub(_d,_i,_i+1),16) local _kb=tonumber(string.sub(_k,((_i-1)/2)%#' . strlen($key) . '*2+1,((_i-1)/2)%#' . strlen($key) . '*2+2),16) _r=_r..string.char(_db~_kb) end return _r end)("' . $hexData . '","' . $hexKey . '")';
            case 'reverse':
                $revData = bin2hex(strrev(base64_decode($data)));
                return '(function(_d) local _r="" for i=#_d,1,-2 do _r=_r..string.char(tonumber(string.sub(_d,i,i+1),16)) end return _r end)("' . $revData . '")';
            default:
                return '(function(_d) local _r="" for i=1,#_d,2 do _r=_r..string.char(tonumber(string.sub(_d,i,i+1),16)) end return _r end)("' . $hexData . '")';
        }
    }

    /* ─── Bash ─── */
    private static function bashDecrypt($data, $key, $mode) {
        switch ($mode) {
            case 'xor':
                $b64 = base64_encode($key);
                return '(echo "' . $data . '" | base64 -d | od -An -tu1 -v | tr -s " " "\n" | while read _b; do _k=$(echo "' . $b64 . '" | base64 -d | od -An -tu1 -v | tr -s " " "\n" | sed -n "$((i%$(echo "' . $b64 . '" | base64 -d | wc -c | tr -d " ")+1))p"); printf "\\$(printf "%d" "$_b" "^" "$_k")" ; i=$((i+1)); done)';
            case 'reverse':
                return '(echo "' . $data . '" | base64 -d | rev)';
            case 'caesar':
                return '(echo "' . $data . '" | base64 -d | tr "A-Z" "$(echo "A-Z" | fold -w' . $key . ' | tail -n+' . ($key + 1) . '; echo "A-Z" | fold -w' . $key . ' | head -n' . $key . ')" | tr "a-z" "$(echo "a-z" | fold -w' . $key . ' | tail -n+' . ($key + 1) . '; echo "a-z" | fold -w' . $key . ' | head -n' . $key . ')" )';
            default:
                return '(echo "' . $data . '" | base64 -d)';
        }
    }

    private static function genericDecrypt($data, $key, $mode) {
        return "base64_decode('$data')";
    }

    private static function polymorphizeStub($stub, $lang) {
        $suffix = RandomProvider::string(4);
        $dummyVal = random_int(1000, 99999);
        switch ($lang) {
            case 'python':
                $dummyVar = '_p' . $suffix;
                $stub = '(' . $dummyVar . ':=' . $dummyVal . ') and ' . $stub;
                break;
            case 'javascript': case 'typescript':
                $dummyVar = '_p' . $suffix;
                $stub = '(function(){var ' . $dummyVar . '=' . $dummyVal . ';return ' . $stub . '})()';
                break;
            case 'php':
                $stub = '(' . $stub . ')';
                break;
            default:
                break;
        }
        return $stub;
    }
}
