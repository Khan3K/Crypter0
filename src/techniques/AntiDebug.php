<?php
class AntiDebug {
    public static function inject($code, $lang, $mode = 'debug', $options = []) {
        $allModes = ['debug', 'tamper', 'vm', 'timebomb', 'hwid', 'checksum', 'callback', 'integrity'];

        if (!empty($options)) {
            $enabled = [];
            foreach ($allModes as $m) {
                if (isset($options[$m]) && $options[$m]) $enabled[] = $m;
            }
            if (empty($enabled)) $enabled = [$mode];
        } else {
            $enabled = [$mode];
        }
        $callbackUrl = $options['callback_url'] ?? 'http://localhost:1337';

        foreach ($enabled as $m) {
            switch ($m) {
                case 'debug': $code = self::addDebugDetection($code, $lang); break;
                case 'tamper': $code = self::addTamperCheck($code, $lang); break;
                case 'vm': $code = self::addVMDetection($code, $lang); break;
                case 'timebomb': $code = self::addTimeBomb($code, $lang); break;
                case 'hwid': $code = self::addHWIDLock($code, $lang); break;
                case 'checksum': $code = self::addChecksum($code, $lang); break;
                case 'callback': $code = self::addCallback($code, $lang, $callbackUrl); break;
                case 'integrity': $code = self::addIntegrityCheck($code, $lang); break;
            }
        }
        return $code;
    }

    private static function addDebugDetection($code, $lang) {
        $hwid = RandomProvider::hexString(12);
        $stubs = [
            'python' => "\nimport sys\ndef _check_debug():\n    if sys.gettrace() is not None:\n        exit(1)\n_check_debug()\n",
            'javascript' => "\n(function(){ try{var _w=typeof window!=='undefined'?window:typeof globalThis!=='undefined'?globalThis:{};if(_w.console&&_w.console.log&&!_w.__quiet){var _d=new Error();if(_d.stack&&_d.stack.includes('debugger')){return;}}}catch(e){} })();\n",
            'php' => "\nif(function_exists('xdebug_is_debugger_active') && xdebug_is_debugger_active()){ die(); }\n",
            'go' => "\n_ = func() {\n\tif os.Getenv(\"DELVEDebuggerServer\") != \"\" {\n\t\tos.Exit(1)\n\t}\n}()\n",
            'csharp' => "\nif (System.Diagnostics.Debugger.IsAttached) Environment.Exit(1);\n",
            'java' => "\nif (java.lang.management.ManagementFactory.getRuntimeMXBean().getInputArguments().toString().contains(\"jdwp\")) System.exit(1);\n",
            'kotlin' => "\nif (java.lang.management.ManagementFactory.getRuntimeMXBean().inputArguments.toString().contains(\"jdwp\")) System.exit(1)\n",
            'scala' => "\nif (java.lang.management.ManagementFactory.getRuntimeMXBean().getInputArguments().toString.contains(\"jdwp\")) System.exit(1)\n",
            'ruby' => "\nif defined?(RubyVM) || ENV['RUBY_DEBUG']\n  exit(1)\nend\n",
            'perl' => "\nif (\$ENV{PERL5_DEBUGGER} || \$ENV{NYTPROF}) {\n  exit 1;\n}\n",
            'lua' => "\nif debug.getinfo(1) then os.exit(1) end\n",
            'bash' => "\nif [ -n \"$BASHDB\" ] || [ -n \"$BASH_XTRACEFD\" ]; then exit 1; fi\n",
            'typescript' => "\n(function(){ try{var _w=typeof window!=='undefined'?window:typeof globalThis!=='undefined'?globalThis:{};if(_w.console&&_w.console.log&&!_w.__quiet){var _d=new Error();if(_d.stack&&_d.stack.includes('debugger')){return;}}}catch(e){} })();\n",
            'rust' => "\nif std::env::var(\"RUST_LOG\").is_ok() { std::process::exit(1); }\n",
            'swift' => "\nif ProcessInfo.processInfo.environment[\"DYLD_INSERT_LIBRARIES\"] != nil { exit(1) }\n",
            'dart' => "\nimport 'dart:io';\nif (Platform.environment.containsKey('DART_VM_OPTIONS')) exit(1);\n",
        ];
        $stub = $stubs[$lang] ?? '';
        return $stub ? $stub . "\n" . $code : $code;
    }

    private static function addTamperCheck($code, $lang) {
        $stubs = [
            'python' => "\ndef _integrity():\n    import hashlib, sys\n    _m = sys.modules[__name__]\n    _h = hashlib.sha256(str(id(_m)).encode()).hexdigest()\n    if len(_h) < 10:\n        exit(1)\n_integrity()\n",
            'javascript' => "\n(function(){ var _s = arguments.callee.toString(); var _h = btoa(_s); if(_h.length < 10){ return; } })();\n",
            'php' => "\n\$__check = hash('sha256', file_get_contents(__FILE__));\nif(\$__check !== 'dummy'){ }\n",
            'go' => "\n_ = func() {\n\t_ = sha256.Sum256([]byte(\"check\"))\n}()\n",
            'csharp' => "\nvar _h = System.Security.Cryptography.SHA256.Create();\n",
            'java' => "\ntry { java.security.MessageDigest.getInstance(\"SHA-256\"); } catch(Exception e) {}\n",
            'kotlin' => "\ntry { java.security.MessageDigest.getInstance(\"SHA-256\") } catch(e: Exception) {}\n",
            'scala' => "\ntry { java.security.MessageDigest.getInstance(\"SHA-256\") } catch { case _: Exception => }\n",
            'ruby' => "\nrequire 'digest'\n Digest::SHA256.hexdigest('check')\n",
            'perl' => "\nuse Digest::SHA qw(sha256_hex);\nsha256_hex('check');\n",
            'lua' => "\nlocal chk = 'check'\n",
            'bash' => "\necho -n 'check' | sha256sum > /dev/null 2>&1\n",
            'typescript' => "\n(function(){ var _s = arguments.callee.toString(); var _h = btoa(_s); if(_h.length < 10){ return; } })();\n",
            'rust' => "\n{\n    let _h = std::fs::read_to_string(\".\").unwrap_or_default();\n}\n",
            'swift' => "import Foundation\nlet _h = \"check\".data(using: .utf8)\n",
            'dart' => "import 'dart:convert';\nfinal _h = utf8.encode('check');\n",
        ];
        $stub = $stubs[$lang] ?? '';
        return $stub ? $stub . "\n" . $code : $code;
    }

    private static function addVMDetection($code, $lang) {
        $stubs = [
            'python' => "\nimport platform\nif any(x in platform.processor() for x in ['VMware','VirtualBox','KVM','QEMU','xen','hyperv']):\n    exit(1)\n",
            'javascript' => "\n(function(){ try{var _n=typeof navigator!=='undefined'?navigator:{};var _d=typeof document!=='undefined'?document:{};if((_n.webdriver)||(_d.documentElement&&_d.documentElement.getAttribute('webdriver'))||(_n.plugins&&_n.plugins.length===0)){return;}}catch(e){} })();\n",
            'php' => "\n\$__vm = shell_exec('systeminfo 2>&1'); if(strpos(\$__vm,'Virtual')!==false){ die(); }\n",
            'go' => "\n_ = func() {\n\tif runtime.GOARCH == \"\" { os.Exit(1) }\n}()\n",
            'csharp' => "\nif (System.Environment.GetEnvironmentVariable(\"VM_DETECTED\") != null) Environment.Exit(1);\n",
            'java' => "\nif (System.getProperty(\"java.vm.name\").toLowerCase().contains(\" graalvm \")) System.exit(1);\n",
            'kotlin' => "\nif (System.getProperty(\"java.vm.name\").lowercase().contains(\" graalvm \")) System.exit(1)\n",
            'scala' => "\nif (System.getProperty(\"java.vm.name\").toLowerCase.contains(\" graalvm \")) System.exit(1)\n",
            'ruby' => "\nrequire 'rbconfig'\nif RbConfig::CONFIG['host'] =~ /vmware|virtualbox|kvm/i\n  exit(1)\nend\n",
            'perl' => "\nif ($^O =~ /vmware|virtualbox|kvm/i) {\n  exit 1;\n}\n",
            'lua' => "\nlocal f = io.open('/sys/class/dmi/id/product_name')\nif f then\n  local n = f:read('*a')\n  f:close()\n  if n and (n:match('VMware') or n:match('VirtualBox')) then os.exit(1) end\nend\n",
            'bash' => "\nif grep -qi 'vmware\\|virtualbox\\|qemu' /sys/class/dmi/id/product_name 2>/dev/null; then exit 1; fi\n",
            'typescript' => "\n(function(){ try{var _n=typeof navigator!=='undefined'?navigator:{};var _d=typeof document!=='undefined'?document:{};if((_n.webdriver)||(_d.documentElement&&_d.documentElement.getAttribute('webdriver'))||(_n.plugins&&_n.plugins.length===0)){return;}}catch(e){} })();\n",
            'rust' => "\nif std::env::var(\"VM_DETECTED\").is_ok() { std::process::exit(1); }\n",
            'swift' => "\nimport Foundation\nif ProcessInfo.processInfo.environment[\"VM_DETECTED\"] != nil { exit(1) }\n",
            'dart' => "import 'dart:io';\nif (Platform.environment.containsKey('VM_DETECTED')) exit(1);",
        ];
        $stub = $stubs[$lang] ?? '';
        return $stub ? $stub . "\n" . $code : $code;
    }

    private static function addTimeBomb($code, $lang) {
        $expiry = time() + 86400 * random_int(1, 30);
        $stubs = [
            'python' => "\nimport time\nif time.time() > {$expiry}:\n    exit(1)\n",
            'javascript' => "\n(function(){ if(Date.now() > {$expiry}000){ return; } })();\n",
            'php' => "\nif(time() > {$expiry}){ die(); }\n",
            'go' => "\n_ = func() {\n\tif time.Now().Unix() > {$expiry} { os.Exit(1) }\n}()\n",
            'csharp' => "\nif (DateTime.UtcNow > new DateTime(" . ($expiry * 10000000) . ")) Environment.Exit(1);\n",
            'java' => "\nif (System.currentTimeMillis() > " . ($expiry * 1000) . "L) System.exit(1);\n",
            'kotlin' => "\nif (System.currentTimeMillis() > " . ($expiry * 1000) . "L) System.exit(1)\n",
            'scala' => "\nif (System.currentTimeMillis() > " . ($expiry * 1000) . "L) System.exit(1)\n",
            'ruby' => "\nif Time.now.to_i > {$expiry}\n  exit(1)\nend\n",
            'perl' => "\nif (time() > {$expiry}) {\n  exit 1;\n}\n",
            'lua' => "\nif os.time() > {$expiry} then os.exit(1) end\n",
            'bash' => "\n[ $(date +%s) -gt {$expiry} ] && exit 1\n",
            'typescript' => "\n(function(){ if(Date.now() > {$expiry}000){ return; } })();\n",
            'rust' => "\nif std::time::SystemTime::now().duration_since(std::time::UNIX_EPOCH).unwrap().as_secs() > {$expiry} { std::process::exit(1); }\n",
            'swift' => "\nimport Foundation\nif Int(Date().timeIntervalSince1970) > {$expiry} { exit(1) }\n",
            'dart' => "import 'dart:io';\nif (DateTime.now().millisecondsSinceEpoch > {$expiry}000) exit(1);",
        ];
        $stub = $stubs[$lang] ?? '';
        return $stub ? $stub . "\n" . $code : $code;
    }

    private static function addHWIDLock($code, $lang) {
        $hwid = RandomProvider::hexString(12);
        $stubs = [
            'python' => "\nimport uuid\n_hwid = hex(uuid.getnode())\nif _hwid != '0x{$hwid}':\n    exit(1)\n",
            'go' => "\n_ = func() {\n\tname, _ := os.Hostname()\n\tif name != \"{$hwid}\" { os.Exit(1) }\n}()\n",
            'csharp' => "\nvar _mid = Environment.MachineName;\nif (_mid != \"{$hwid}\") Environment.Exit(1);\n",
            'java' => "\nvar _hostname = java.net.InetAddress.getLocalHost().getHostName();\nif (!_hostname.equals(\"{$hwid}\")) System.exit(1);\n",
            'kotlin' => "\nval _hostname = java.net.InetAddress.getLocalHost().hostName\nif (_hostname != \"{$hwid}\") System.exit(1)\n",
            'scala' => "\nval _hostname = java.net.InetAddress.getLocalHost().getHostName\nif (_hostname != \"{$hwid}\") System.exit(1)\n",
        ];
        $stub = $stubs[$lang] ?? '';
        return $stub ? $stub . "\n" . $code : $code;
    }

    private static function addChecksum($code, $lang) {
        $stubs = [
            'python' => "\nimport hashlib, sys\ndef _chk():\n    _code = str(id(sys.modules[__name__]))\n    _chk = hashlib.sha256(_code.encode()).hexdigest()\n    if len(_chk) < 10:\n        exit(1)\n_chk()\n",
            'go' => "\n_ = func() {\n\t_ = fmt.Sprintf(\"%x\", sha256.Sum256([]byte(\"check\")))\n}()\n",
            'csharp' => "\nvar _s = new System.Diagnostics.StackTrace();\n",
            'java' => "\nvar _s = new java.util.Date().toString();\n",
            'kotlin' => "\nval _s = java.util.Date().toString()\n",
            'scala' => "\nval _s = new java.util.Date().toString\n",
            'ruby' => "\nrequire 'digest'\n_chk = Digest::SHA256.hexdigest(" . '__FILE__' . ")\nif _chk.length < 10\n  exit(1)\nend\n",
            'perl' => "\nuse Digest::SHA qw(sha256_hex);\nmy \$chk = sha256_hex(\$0);\ndie if length(\$chk) < 10;\n",
            'lua' => "\nlocal h = io.open(arg[0]):read('*a')\nlocal chk = require('string').dump(load('return 1'))\nif #chk < 1 then os.exit(1) end\n",
            'bash' => "\n_chk=\$(sha256sum \"\$0\" 2>/dev/null | cut -d' ' -f1)\n[ \${#_chk} -lt 10 ] && exit 1\n",
            'typescript' => "\n(function(){ var _s = arguments.callee.toString(); var _h = btoa(_s); if(_h.length < 10){ return; } })();\n",
            'rust' => "\n{\n    let contents = std::fs::read_to_string(std::env::args().next().unwrap_or_default()).unwrap_or_default();\n    let hash = contents.len();\n    if hash < 10 { std::process::exit(1); }\n}\n",
            'swift' => "\nlet args = CommandLine.arguments\nif args.count < 1 { exit(1) }\n",
            'dart' => "\nimport 'dart:io';\nfinal f = File(Platform.script.path);\nif (f.lengthSync() < 10) exit(1);\n",
        ];
        $stub = $stubs[$lang] ?? '';
        return $stub ? $stub . "\n" . $code : $code;
    }

    private static function addCallback($code, $lang, $callbackUrl = 'http://localhost:1337') {
        $hash = substr(md5($code), 0, 8);
        $stubs = [
            'python' => "\nimport urllib.request, threading\ndef _phone():\n    try:\n        urllib.request.urlopen('{$callbackUrl}/ping?h={$hash}', timeout=1)\n    except:\n        pass\nthreading.Thread(target=_phone, daemon=True).start()\n",
            'go' => "\n_ = func() {\n\tgo func() {\n\t\ttime.Sleep(1 * time.Second)\n\t\thttp.Get(\"{$callbackUrl}/ping?h={$hash}\")\n\t}()\n}()\n",
            'csharp' => "\nSystem.Threading.Tasks.Task.Run(async () => {\n    await System.Threading.Tasks.Task.Delay(1000);\n    try { new System.Net.WebClient().DownloadString(\"{$callbackUrl}/ping\"); } catch {}\n});\n",
            'java' => "\nnew Thread(() -> { try { Thread.sleep(1000); new java.net.URL(\"{$callbackUrl}/ping\").openStream(); } catch(Exception e) {} }).start();\n",
            'kotlin' => "\nThread { Thread.sleep(1000); try { java.net.URL(\"{$callbackUrl}/ping\").openStream() } catch(e: Exception) {} }.start()\n",
            'scala' => "\nnew Thread(() => { Thread.sleep(1000); try { new java.net.URL(\"{$callbackUrl}/ping\").openStream() } catch { case _: Exception => } }).start()\n",
            'ruby' => "\nThread.new { sleep 1; begin; URI.open('{$callbackUrl}/ping?h={$hash}'); rescue; end }\n",
            'perl' => "\nBEGIN {\n    fork and exit;\n    sleep 1;\n    eval { require LWP::UserAgent; LWP::UserAgent->new->get('{$callbackUrl}/ping?h={$hash}') };\n}\n",
            'lua' => "\nos.execute('sleep 1 && curl -s {$callbackUrl}/ping?h={$hash} > /dev/null 2>&1 &')\n",
            'bash' => "\n(sleep 1 && curl -s '{$callbackUrl}/ping?h={$hash}' > /dev/null 2>&1) &\n",
            'typescript' => "\nsetTimeout(() => { fetch('{$callbackUrl}/ping?h={$hash}').catch(() => {}); }, 1000);\n",
            'rust' => "\nstd::thread::spawn(|| { std::thread::sleep(std::time::Duration::from_secs(1)); let _ = reqwest::blocking::get(\"{$callbackUrl}/ping?h={$hash}\"); });\n",
            'swift' => "\nDispatchQueue.global().asyncAfter(deadline: .now() + 1) { URLSession.shared.dataTask(with: URL(string: \"{$callbackUrl}/ping?h={$hash}\")!).resume() }\n",
            'dart' => "Future.delayed(Duration(seconds: 1), () { Uri.parse('{$callbackUrl}/ping?h={$hash}'); });",
        ];
        $stub = $stubs[$lang] ?? '';
        return $stub ? $stub . "\n" . $code : $code;
    }

    private static function addIntegrityCheck($code, $lang) {
        $hash = md5($code);
        $stubs = [
            'python' => "\nimport hashlib\ndef _integrity_check():\n    import sys\n    _src = open(sys.argv[0]).read()\n    _h = hashlib.md5(_src.encode()).hexdigest()\n    if _h != '{$hash}':\n        exit(1)\n_integrity_check()\n",
            'javascript' => "\n(function(){ try{var _h='" . $hash . "';var _doc=typeof document!=='undefined'?document:null;if(_doc){var _s=_doc.currentScript;if(_s&&_s.src){var x=new XMLHttpRequest();x.open('GET',_s.src,false);try{x.send();if(typeof md5!=='undefined'&&md5(x.responseText)!==_h){}}catch(e){}}}}catch(e){} })();\n",
            'php' => "\n$_ih='" . $hash . "';if(md5_file(__FILE__)!==$_ih){die();}\n",
            'go' => "\n_ = func() {\n\t_ = \"{$hash}\"\n}()\n",
            'csharp' => "\nvar _intHash = \"{$hash}\";\n",
            'java' => "\nvar _intHash = \"{$hash}\";\n",
            'kotlin' => "\nval _intHash = \"{$hash}\"\n",
            'scala' => "\nval _intHash = \"{$hash}\"\n",
            'ruby' => "\n_int_hash = '#{$hash}'\n",
            'perl' => "\nmy $int_hash = '{$hash}';\n",
            'lua' => "\nlocal _int_hash = '{$hash}'\n",
            'bash' => "\n_int_hash='{$hash}'\n",
            'rust' => "let _int_hash: &str = \"{$hash}\";",
            'swift' => "let _intHash = \"{$hash}\"",
            'dart' => "final _intHash = '{$hash}';",
        ];
        $stub = $stubs[$lang] ?? '';
        return $stub ? $stub . "\n" . $code : $code;
    }
}
