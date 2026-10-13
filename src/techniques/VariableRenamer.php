<?php
class VariableRenamer {
    public static function process($code, $lang, $mode = 'simple', $options = []) {
        $style = $options['style'] ?? $mode;
        switch ($lang) {
            case 'python': return self::renamePython($code, $style);
            case 'javascript': case 'typescript': return self::renameJavaScript($code, $style);
            case 'php': return self::renamePHP($code, $style);
            case 'ruby': return self::renameRuby($code, $style);
            case 'go': return self::renameGo($code, $style);
            case 'csharp': return self::renameCSharp($code, $style);
            case 'java': return self::renameJava($code, $style);
            case 'kotlin': return self::renameKotlin($code, $style);
            case 'scala': return self::renameScala($code, $style);
            case 'perl': return self::renamePerl($code, $style);
            case 'lua': return self::renameLua($code, $style);
            case 'bash': return self::renameBash($code, $style);
            default: return $code;
        }
    }

    private static function safeVarName($length = 12) {
        $first = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_';
        $rest = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
        $name = $first[random_int(0, strlen($first) - 1)];
        for ($i = 1; $i < $length; $i++) $name .= $rest[random_int(0, strlen($rest) - 1)];
        return $name;
    }

    private static function renamePython($code, $mode) {
        $keywords = ['and','or','not','if','else','elif','for','while','def','class','return','import','from','as','in','is','lambda','with','try','except','finally','raise','yield','global','nonlocal','pass','break','continue','True','False','None','self','print','len','range','int','str','list','dict','tuple','set','float','bool','abs','max','min','sum','all','any','map','filter','zip','enumerate','sorted','reversed','type','isinstance','hasattr','getattr','setattr','delattr','open','file','input','raw_input','exec','eval','compile','__import__','del','assert','async','await'];

        $strings = [];
        $origCode = $code;
        $code = preg_replace_callback('/("""[\s\S]*?"""|\'\'\'[\s\S]*?\'\'\'|"(?:[^"\\\\]|\\\\.)*"|\'(?:[^\'\\\\]|\\\\.)*\')/', function($m) use (&$strings) {
            $id = '__CRYPTSTR' . count($strings) . '__';
            $strings[$id] = $m[0];
            return $id;
        }, $code);
        if ($code === null) return $origCode;

        $vars = [];
        if ($mode === 'unicode' || $mode === 'full') {
            $skipNames = [];
            preg_match_all('/^\s*(?:import|from)\s+(.+)/m', $code, $importLines);
            foreach ($importLines[1] as $line) {
                preg_match_all('/\b([a-zA-Z_]\w*)\b/', $line, $names);
                foreach ($names[1] as $n) {
                    if (!in_array($n, ['import', 'from', 'as', 'if', 'else', 'elif'])) $skipNames[$n] = true;
                }
            }
            preg_match_all('/\.([a-zA-Z_]\w*)\b/', $code, $dotMatches);
            foreach ($dotMatches[1] as $dm) $skipNames[$dm] = true;

            $varPattern = '/\b([a-zA-Z_][a-zA-Z0-9_]*)\b/';
            preg_match_all($varPattern, $code, $matches);
            foreach ($matches[1] as $v) {
                if (!in_array($v, $keywords) && strlen($v) > 1 && strpos($v, '__CRYPTSTR') === false && !isset($skipNames[$v])) $vars[$v] = true;
            }
        } else {
            $assignPattern = '/(?:^|\n)\s*([a-zA-Z_]\w*)\s*=\s*/m';
            $forPattern = '/for\s+(\w+)\s+in\b/';
            $withPattern = '/with\s+(\w+)\s+as\b/';
            preg_match_all($assignPattern, $code, $m1);
            preg_match_all($forPattern, $code, $m3);
            preg_match_all($withPattern, $code, $m4);
            foreach ($m1[1] as $v) {
                if (!in_array($v, $keywords) && strlen($v) > 1) $vars[$v] = true;
            }
            foreach ($m3[1] as $v) {
                if (!in_array($v, $keywords) && strlen($v) > 1) $vars[$v] = true;
            }
            foreach ($m4[1] as $v) {
                if (!in_array($v, $keywords) && strlen($v) > 1) $vars[$v] = true;
            }
        }

        $mapping = [];
        foreach (array_keys($vars) as $v) {
            $mapping[$v] = $mode === 'unicode' ? RandomProvider::unicodeVar() : self::safeVarName(random_int(8, 20));
        }

        foreach ($mapping as $old => $new) {
            $code = preg_replace('/\b' . preg_quote($old, '/') . '\b/', $new, $code);
        }

        foreach ($strings as $id => $original) {
            $code = str_replace($id, $original, $code);
        }
        return $code;
    }

    private static function renameJavaScript($code, $mode) {
        $varPattern = '/(?:var|let|const)\s+(\w+)|function\s+(\w+)|(\w+)\s*=\s*(?:function|\(|=>)/';
        $keywords = ['var','let','const','function','return','if','else','for','while','do','switch','case','break','continue','new','this','typeof','instanceof','void','delete','in','of','class','extends','super','import','export','default','from','as','try','catch','finally','throw','async','await','yield','null','undefined','true','false','NaN','Infinity','console','document','window','Array','Object','String','Number','Boolean','Math','Date','RegExp','Map','Set','Promise','Symbol','JSON','parseInt','parseFloat','isNaN','isFinite','eval','require','module','exports','process','Buffer','setTimeout','setInterval','clearTimeout','clearInterval'];
        preg_match_all($varPattern, $code, $matches);
        $vars = [];
        foreach ($matches[1] as $v) { if ($v && !in_array($v, $keywords)) $vars[$v] = true; }
        foreach ($matches[2] as $v) { if ($v && !in_array($v, $keywords)) $vars[$v] = true; }
        foreach ($matches[3] as $v) { if ($v && !in_array($v, $keywords)) $vars[$v] = true; }
        $mapping = [];
        foreach (array_keys($vars) as $v) {
            $mapping[$v] = $mode === 'unicode' ? ('$' . RandomProvider::unicodeVar()) : ('_' . RandomProvider::string(random_int(6, 16), 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_'));
        }
        $used = [];
        foreach ($mapping as $old => $new) {
            if (isset($used[$old])) continue;
            $used[$old] = true;
            $code = preg_replace('/\b' . preg_quote($old, '/') . '\b/', $new, $code);
        }
        return $code;
    }

    private static function renamePHP($code, $mode) {
        $varPattern = '/\$([a-zA-Z_][a-zA-Z0-9_]*)/';
        $keywords = ['this','GLOBALS','_SERVER','_GET','_POST','_FILES','_REQUEST','_SESSION','_ENV','_COOKIE','php_errormsg','http_response_header','argc','argv'];
        preg_match_all($varPattern, $code, $matches);
        $vars = [];
        foreach ($matches[1] as $v) {
            if (!in_array($v, $keywords) && strlen($v) > 2) $vars[$v] = true;
        }
        $mapping = [];
        foreach (array_keys($vars) as $v) {
            $mapping['$' . $v] = '$' . ($mode === 'unicode' ? RandomProvider::unicodeVar() : self::safeVarName(random_int(8, 20)));
        }
        foreach ($mapping as $old => $new) {
            $code = preg_replace('/' . preg_quote($old, '/') . '\b/', $new, $code);
        }
        return $code;
    }

    private static function renameRuby($code, $mode) {
        $varPattern = '/(?:@{1,2})?([a-z_][a-zA-Z0-9_]*)\s*=|def\s+(\w+)/';
        $keywords = ['def','class','module','if','else','elsif','unless','while','until','for','in','do','end','return','yield','nil','true','false','self','super','and','or','not','require','include','extend','attr_reader','attr_writer','attr_accessor','puts','print','gets','chomp','to_s','to_i','to_f','each','map','select','reject','inject','reduce','new','initialize','raise','begin','rescue','ensure','throw','catch','lambda','proc','Proc','Array','Hash','String','Integer','Float','Symbol','File','Dir','Math','Time','Date','rand','loop','sleep','exit','abort','load','require_relative','require','private','public','protected','alias','undef'];
        preg_match_all($varPattern, $code, $matches);
        $vars = [];
        foreach ($matches[1] as $v) { if ($v && !in_array($v, $keywords) && strlen($v) > 2) $vars[$v] = true; }
        foreach ($matches[2] as $v) { if ($v && !in_array($v, $keywords)) $vars[$v] = true; }
        $mapping = [];
        foreach (array_keys($vars) as $v) {
            $mapping[$v] = $mode === 'unicode' ? RandomProvider::unicodeVar() : ('_' . RandomProvider::string(random_int(6, 16)));
        }
        foreach ($mapping as $old => $new) {
            $code = preg_replace('/\b' . preg_quote($old, '/') . '\b(?!\s*\()/', $new, $code);
        }
        return $code;
    }

    private static function renameGo($code, $mode) {
        $varPattern = '/(?:var\s+)?(\w+)\s*(?:=|:=)|func\s+(\w+)/';
        $keywords = ['package','import','func','var','const','type','struct','interface','map','chan','go','defer','select','range','if','else','for','switch','case','default','break','continue','return','goto','fallthrough','nil','true','false','make','new','append','len','cap','copy','close','delete','panic','recover','print','println','fmt','int','string','bool','float64','byte','rune','error','uint','int8','int16','int32','int64','uint8','uint16','uint32','uint64','float32','complex64','complex128','uintptr','string','bool','byte','rune','error','io','os','net','http','json','ioutil','bufio','time','sort','math','strconv','sync','atomic'];
        preg_match_all($varPattern, $code, $matches);
        $vars = [];
        foreach ($matches[1] as $v) { if ($v && !in_array($v, $keywords) && strlen($v) > 2) $vars[$v] = true; }
        foreach ($matches[2] as $v) { if ($v && !in_array($v, $keywords)) $vars[$v] = true; }
        $mapping = [];
        foreach (array_keys($vars) as $v) {
            $mapping[$v] = '_' . RandomProvider::string(random_int(6, 16));
        }
        foreach ($mapping as $old => $new) {
            $code = preg_replace('/\b' . preg_quote($old, '/') . '\b(?!\s*\()/', $new, $code);
        }
        return $code;
    }

    private static function renameCSharp($code, $mode) {
        $varPattern = '/(?:int|string|bool|var|char|long|double|float|decimal|object|byte|short|sbyte|uint|ulong|ushort|public|private|protected|internal|static|readonly|const|override|virtual|abstract|async|sealed|partial|new|required)\s+(\w+)|(\w+)\s*\(|(\w+)\s*=>/';
        $keywords = ['using','namespace','class','struct','interface','enum','void','return','if','else','for','foreach','while','do','switch','case','default','break','continue','try','catch','finally','throw','new','this','base','typeof','sizeof','nameof','is','as','in','out','ref','params','select','where','from','orderby','group','into','let','var','true','false','null','yield','async','await','get','set','add','remove','value','event','delegate','operator','implicit','explicit',' checked','unchecked','fixed','stackalloc','sizeof','Console','String','Int32','Boolean','Object','Array','List','Dictionary','Exception','Math','Convert','Environment','Guid','DateTime','TimeSpan','Task','Action','Func','IEnumerable','IList','IDictionary'];
        preg_match_all($varPattern, $code, $matches);
        $vars = [];
        foreach ($matches[1] as $v) { if ($v && !in_array($v, $keywords) && strlen($v) > 2) $vars[$v] = true; }
        foreach ($matches[2] as $v) { if ($v && !in_array($v, $keywords) && strlen($v) > 2) $vars[$v] = true; }
        $mapping = [];
        foreach (array_keys($vars) as $v) {
            $mapping[$v] = '_' . RandomProvider::string(random_int(6, 16));
        }
        foreach ($mapping as $old => $new) {
            $code = preg_replace('/\b' . preg_quote($old, '/') . '\b/', $new, $code);
        }
        return $code;
    }

    private static function renameJava($code, $mode) {
        $varPattern = '/(?:int|String|boolean|var|byte|short|long|double|float|char|void|public|private|protected|static|final|abstract|synchronized|volatile|transient|native|strictfp)\s+(\w+)|(\w+)\s*\(/';
        $keywords = ['import','package','class','interface','enum','extends','implements','return','if','else','for','foreach','while','do','switch','case','default','break','continue','try','catch','finally','throw','new','this','super','typeof','instanceof','void','null','true','false','static','final','abstract','synchronized','volatile','transient','native','strictfp','public','private','protected','System','String','Integer','Boolean','Object','Arrays','Collections','List','Map','Set','ArrayList','HashMap','HashSet','Exception','RuntimeException','Thread','Math','Long','Double','Float','Character','Byte','Short','Integer','Override'];
        preg_match_all($varPattern, $code, $matches);
        $vars = [];
        foreach ($matches[1] as $v) { if ($v && !in_array($v, $keywords) && strlen($v) > 2) $vars[$v] = true; }
        foreach ($matches[2] as $v) { if ($v && !in_array($v, $keywords) && strlen($v) > 2) $vars[$v] = true; }
        $mapping = [];
        foreach (array_keys($vars) as $v) {
            $mapping[$v] = '_' . RandomProvider::string(random_int(6, 16));
        }
        foreach ($mapping as $old => $new) {
            $code = preg_replace('/\b' . preg_quote($old, '/') . '\b/', $new, $code);
        }
        return $code;
    }

    private static function renameKotlin($code, $mode) {
        $varPattern = '/(?:val|var)\s+(\w+)|fun\s+(\w+)|lazy\s+val\s+(\w+)/';
        $keywords = ['fun','val','var','class','interface','object','data','sealed','enum','abstract','open','internal','private','protected','public','override','return','if','else','for','while','do','when','is','as','in','try','catch','finally','throw','new','this','super','companion','suspend','inline','noinline','crossinline','reified','typealias','by','lazy','init','constructor','get','set','field','it','true','false','null','println','print','listOf','mapOf','setOf','arrayOf','intArrayOf','String','Int','Boolean','Long','Double','Float','Char','Byte','Short','Any','Nothing','Unit','List','MutableList','Map','MutableMap','Set','MutableSet','Array','ArrayList','HashMap','HashSet','Exception'];
        preg_match_all($varPattern, $code, $matches);
        $vars = [];
        foreach ($matches[1] as $v) { if ($v && !in_array($v, $keywords) && strlen($v) > 2) $vars[$v] = true; }
        foreach ($matches[2] as $v) { if ($v && !in_array($v, $keywords) && strlen($v) > 2) $vars[$v] = true; }
        $mapping = [];
        foreach (array_keys($vars) as $v) {
            $mapping[$v] = '_' . RandomProvider::string(random_int(6, 16));
        }
        foreach ($mapping as $old => $new) {
            $code = preg_replace('/\b' . preg_quote($old, '/') . '\b/', $new, $code);
        }
        return $code;
    }

    private static function renameScala($code, $mode) {
        $varPattern = '/(?:val|var)\s+(\w+)|def\s+(\w+)|lazy\s+val\s+(\w+)/';
        $keywords = ['val','var','def','class','trait','object','abstract','extends','with','return','if','else','for','while','do','match','case','yield','try','catch','finally','throw','new','this','super','override','private','protected','public','final','lazy','sealed','implicit','inline','given','using','type','enum','true','false','null','println','print','String','Int','Boolean','Long','Double','Float','Char','Byte','Short','Any','AnyRef','AnyVal','Nothing','Unit','Option','Some','None','List','Map','Set','Array','Seq','Vector','mutable','immutable','println','scala','java','sys'];
        preg_match_all($varPattern, $code, $matches);
        $vars = [];
        foreach ($matches[1] as $v) { if ($v && !in_array($v, $keywords) && strlen($v) > 2) $vars[$v] = true; }
        foreach ($matches[2] as $v) { if ($v && !in_array($v, $keywords) && strlen($v) > 2) $vars[$v] = true; }
        $mapping = [];
        foreach (array_keys($vars) as $v) {
            $mapping[$v] = '_' . RandomProvider::string(random_int(6, 16));
        }
        foreach ($mapping as $old => $new) {
            $code = preg_replace('/\b' . preg_quote($old, '/') . '\b/', $new, $code);
        }
        return $code;
    }

    private static function renamePerl($code, $mode) {
        $varPattern = '/[\$@%]([a-zA-Z_][a-zA-Z0-9_]*)/';
        $keywords = ['my','our','local','sub','package','use','require','if','elsif','else','unless','for','foreach','while','until','do','last','next','redo','return','shift','unshift','push','pop','split','join','grep','map','sort','print','warn','die','say','chomp','chop','substr','index','rindex','length','substr','sprintf','printf','open','close','read','write','seek','tell','eof','glob','readdir','closedir','opendir','mkdir','rmdir','unlink','rename','copy','stat','lstat','filetest','abs','length','substr','index','rindex','sprintf','printf','print','warn','die','exit','eval','require','use','no','package','class','method','sub'];
        preg_match_all($varPattern, $code, $matches);
        $vars = [];
        foreach ($matches[1] as $v) {
            if (!in_array($v, $keywords) && strlen($v) > 2) $vars[$v] = true;
        }
        $mapping = [];
        foreach (array_keys($vars) as $v) {
            $mapping[$v] = '_' . RandomProvider::string(random_int(6, 16));
        }
        foreach ($mapping as $old => $new) {
            $code = preg_replace('/\$' . preg_quote($old, '/') . '\b/', '$' . $new, $code);
        }
        return $code;
    }

    private static function renameLua($code, $mode) {
        $varPattern = '/(?:local\s+)?(\w+)\s*=|function\s+(\w+)/';
        $keywords = ['local','function','return','if','then','else','elseif','for','while','do','repeat','until','in','end','break','nil','true','false','and','or','not','print','require','pcall','xpcall','error','assert','type','tostring','tonumber','pairs','ipairs','next','select','unpack','rawget','rawset','setmetatable','getmetatable','table','string','math','io','os','coroutine','debug','string','table','math','io','os'];
        preg_match_all($varPattern, $code, $matches);
        $vars = [];
        foreach ($matches[1] as $v) { if ($v && !in_array($v, $keywords) && strlen($v) > 2) $vars[$v] = true; }
        foreach ($matches[2] as $v) { if ($v && !in_array($v, $keywords) && strlen($v) > 2) $vars[$v] = true; }
        $mapping = [];
        foreach (array_keys($vars) as $v) {
            $mapping[$v] = '_' . RandomProvider::string(random_int(6, 16));
        }
        foreach ($mapping as $old => $new) {
            $code = preg_replace('/\b' . preg_quote($old, '/') . '\b/', $new, $code);
        }
        return $code;
    }

    private static function renameBash($code, $mode) {
        $varPattern = '/(\$\w+)|(\$\{[^}]+\})/';
        $keywords = ['$PATH','$HOME','$USER','$SHELL','$PWD','$OLDPWD','$IFS','$RANDOM','$LINENO','$BASH','$BASH_VERSION','$BASH_SOURCE','$FUNCNAME','$BASH_LINENO','$BASH_COMMAND','$BASH_REMATCH','$BASH_SUBSHELL','$BASH_ARGV','$BASH_ARGC','$PIPESTATUS','$SECONDS','$EUID','$UID','$HOSTNAME','$HOSTTYPE','$MACHTYPE','$OSTYPE','$PPID','$PS1','$PS2','$PS3','$PS4','$PROMPT_COMMAND','$HISTFILE','$HISTSIZE','$HISTFILESIZE','$TMOUT','$LANG','$LC_ALL','$LC_CTYPE','$LC_COLLATE','$LC_MESSAGES','$LC_NUMERIC','$LC_MONETARY','$LC_TIME','$LC_PAPER','$LC_NAME','$LC_ADDRESS','$LC_TELEPHONE','$LC_IDENTIFICATION','$TERM','$DISPLAY','$EDITOR','$VISUAL','$HOME','$USER','$LOGNAME','$MAIL','$SHELL'];
        preg_match_all($varPattern, $code, $matches);
        $vars = [];
        foreach ($matches[1] as $v) {
            $name = substr($v, 1);
            if (!in_array($v, $keywords) && strlen($name) > 1) $vars[$v] = true;
        }
        foreach ($matches[2] as $v) {
            $name = trim(substr($v, 2, -1));
            if (!in_array($v, $keywords) && strlen($name) > 1) $vars[$v] = true;
        }
        $mapping = [];
        foreach (array_keys($vars) as $v) {
            $mapping[$v] = '$_' . RandomProvider::string(random_int(4, 10), 'abcdefghijklmnopqrstuvwxyz');
        }
        foreach ($mapping as $old => $new) {
            $code = str_replace($old, $new, $code);
        }
        return $code;
    }
}
