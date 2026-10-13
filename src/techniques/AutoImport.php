<?php
class AutoImport {
    public static function process($code, $lang, $options = []) {
        $modules = self::detectImports($code, $lang);
        if (empty($modules)) return $code;
        $preamble = self::generatePreamble($modules, $lang);
        return $preamble . "\n" . $code;
    }

    private static function detectImports($code, $lang) {
        $modules = [];
        $stdLib = self::getStdLib($lang);

        switch ($lang) {
            case 'python':
                if (preg_match_all('/^\s*import\s+([\w.]+)/m', $code, $m)) {
                    foreach ($m[1] as $mod) {
                        $base = explode('.', $mod)[0];
                        if (!in_array($base, $stdLib)) $modules[] = $base;
                    }
                }
                if (preg_match_all('/^\s*from\s+([\w.]+)\s+import/m', $code, $m)) {
                    foreach ($m[1] as $mod) {
                        $base = explode('.', $mod)[0];
                        if (!in_array($base, $stdLib)) $modules[] = $base;
                    }
                }
                break;
            case 'javascript': case 'typescript':
                if (preg_match_all('/require\s*\(\s*[\'"]([^\'"\.\/][^\'"]*)[\'"]\s*\)/', $code, $m)) {
                    foreach ($m[1] as $mod) {
                        $base = explode('/', $mod)[0];
                        if ($base[0] !== '@' || !in_array($base, $stdLib)) {
                            if (!in_array($base, $stdLib)) $modules[] = $mod;
                        }
                    }
                }
                if (preg_match_all('/from\s+[\'"]([^\'"\.\/][^\'"]*)[\'"]/', $code, $m)) {
                    foreach ($m[1] as $mod) {
                        if (!in_array($mod, $stdLib)) $modules[] = $mod;
                    }
                }
                break;
            case 'ruby':
                if (preg_match_all('/^\s*require\s+[\'"]([^\'"]+)[\'"]/m', $code, $m)) {
                    foreach ($m[1] as $mod) {
                        if (!in_array($mod, $stdLib)) $modules[] = $mod;
                    }
                }
                break;
            case 'perl':
                if (preg_match_all('/^\s*(?:use|require)\s+([\w:]+)/m', $code, $m)) {
                    foreach ($m[1] as $mod) {
                        if (!in_array($mod, $stdLib)) $modules[] = $mod;
                    }
                }
                break;
            case 'go':
                if (preg_match_all('/^\s*import\s+(?:\(\s*)?["]([^"]+)["]/m', $code, $m)) {
                    foreach ($m[1] as $mod) {
                        $parts = explode('/', $mod);
                        $base = end($parts);
                        if (!in_array($mod, $stdLib) && strpos($mod, 'internal/') === false) {
                            $modules[] = $mod;
                        }
                    }
                }
                break;
        }
        return array_unique(array_filter($modules));
    }

    private static function getStdLib($lang) {
        switch ($lang) {
            case 'python':
                return ['os','sys','re','json','math','time','datetime','collections','itertools',
                    'functools','operator','string','io','pathlib','shutil','subprocess','threading',
                    'multiprocessing','socket','http','urllib','email','html','xml','csv','configparser',
                    'argparse','logging','unittest','hashlib','hmac','secrets','base64','binascii',
                    'struct','codecs','locale','gettext','unicodedata','textwrap','difflib','enum',
                    'dataclasses','typing','abc','contextlib','weakref','copy','pprint','numbers',
                    'cmath','decimal','fractions','random','statistics','array','queue','heapq',
                    'bisect','inspect','site','builtins','gc','traceback','warnings','atexit',
                    'pickle','sqlite3','zlib','gzip','zipfile','tarfile','tempfile','glob','fnmatch',
                    'signal','mmap','ctypes','concurrent','asyncio','venv','struct','typing',
                    'warnings','weakref','xmlrpc','zipimport','__main__','pkgutil','platform',
                    'tokenize','linecache','dis','keyword','token','compileall','code','codeop',
                    'unittest','test','idlelib','tkinter','distutils','setuptools','pip'];
            case 'javascript': case 'typescript':
                return ['fs','path','http','https','net','tls','crypto','os','child_process','cluster',
                    'dgram','dns','events','readline','stream','url','util','vm','zlib','buffer',
                    'querystring','assert','console','process','v8','worker_threads','perf_hooks',
                    'string_decoder','punycode','domain','module','timers','async_hooks','inspector'];
            case 'ruby':
                return ['rubygems','bundler','pathname','fileutils','tmpdir','open-uri','net/http',
                    'uri','json','yaml','csv','re','date','time','digest','openssl','base64','set',
                    'ostruct','pp','prettyprint','English','abbrev','enumerator','fiber','mutex_m',
                    'observer','singleton','tsort','forwardable','delegate','optparse','drb','erb',
                    'irb','rdoc','ri'];
            case 'perl':
                return ['strict','warnings','vars','utf8','feature','Carp','Exporter','File::Spec',
                    'File::Path','File::Basename','File::Find','Cwd','POSIX','Encode','Storable',
                    'Data::Dumper','Getopt::Long','Getopt::Std','IO::File','IO::Socket','HTTP::Tiny',
                    'JSON::PP','Scalar::Util','List::Util','Math::BigInt','Digest::SHA','MIME::Base64',
                    'ExtUtils::MakeMaker','Test::More','Test::Simple','DBI','DBD::SQLite',
                    'LWP::UserAgent','XML::Simple','YAML','Moose','Moo','Dancer2','Mojolicious',
                    'Plack','Template','DBIx::Class','Log::Log4perl','Class::Load','Module::Build',
                    'Dist::Zilla','App::cpanminus'];
            case 'go':
                return ['fmt','os','io','strings','strconv','math','time','net','net/http',
                    'encoding/json','encoding/csv','encoding/xml','encoding/base64','encoding/hex',
                    'crypto','crypto/md5','crypto/sha256','crypto/aes','crypto/rand','crypto/tls',
                    'sync','sync/atomic','context','errors','log','flag','testing','reflect','unsafe',
                    'sort','bytes','bufio','path','path/filepath','runtime','syscall','database/sql',
                    'html/template','text/template','compress/gzip','compress/zlib','archive/tar',
                    'archive/zip','container/list','container/heap','embed','hash','hash/crc32',
                    'index/suffixarray','go/ast','go/format','go/parser','go/token','go/types',
                    'debug/elf','debug/gosym','debug/macho','debug/pe'];
            default:
                return [];
        }
    }

    private static function generatePreamble($modules, $lang) {
        switch ($lang) {
            case 'python':
                $mods = implode("','", array_map(function($m) { return addslashes($m); }, $modules));
                return <<<PY
import subprocess, sys, importlib
def _auto_install():
    _pkgs = ['{$mods}']
    for _p in _pkgs:
        try:
            importlib.import_module(_p.replace('-','_'))
        except ImportError:
            subprocess.check_call([sys.executable, '-m', 'pip', 'install', _p.replace('-','_'), '--quiet'])
_auto_install()
del _auto_install

PY;
            case 'javascript': case 'typescript':
                $mods = implode("','", array_map(function($m) { return addslashes($m); }, $modules));
                return <<<JS
(function(){
var _pkgs=['{$mods}'];
var _isNode=typeof process!=='undefined'&&process.versions&&process.versions.node;
if(!_isNode)return;
for(var i=0;i<_pkgs.length;i++){
  try{require.resolve(_pkgs[i])}catch(e){
    try{require('child_process').execSync('npm install '+_pkgs[i]+' --no-save --silent',{stdio:'ignore'})}catch(e2){}
  }
}
})();

JS;
            case 'ruby':
                $mods = implode("','", array_map(function($m) { return addslashes($m); }, $modules));
                return <<<RB
begin
  ['{$mods}'].each do |m|
    begin; require m; rescue LoadError
      system('gem install ' + m.gsub('_','-') + ' --no-document --quiet')
      begin; require m; rescue LoadError; end
    end
  end
rescue; end

RB;
            case 'perl':
                $modsList = array_map(function($m) { return addslashes($m); }, $modules);
                $perlStr = implode("' '", $modsList);
                return "eval {\n    for my \$m ('$perlStr') {\n        eval \"require \$m\" or system(\"cpan -T \$m 2>/dev/null\");\n    }\n};\n";
            case 'go':
                $lines = [];
                foreach ($modules as $mod) {
                    $lines[] = "// go get {$mod}";
                }
                return implode("\n", $lines) . "\n";
            default:
                return '';
        }
    }
}
