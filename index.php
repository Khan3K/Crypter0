<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Crypter0 — Advanced Code Obfuscation Engine</title>
<link rel="stylesheet" href="assets/css/style.css">
<meta name="theme-color" content="#08080d">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
</head>
<body>
<div class="app">
<header class="hdr">
  <div class="hdr-logo"><div class="ico">C</div><span><span class="ac">Crypter</span>0</span></div>
  <div class="hdr-actions">
    <select class="sel" id="protect-level" style="width:120px;font-size:10px">
      <option value="0">Custom Pipeline</option>
      <option value="1">L1 Minimal</option><option value="2">L2 Light</option><option value="3">L3 Basic</option>
      <option value="4">L4 Standard</option><option value="5">L5 Enhanced</option><option value="6">L6 Advanced</option>
      <option value="7">L7 Enterprise</option><option value="8">L8 Hardened</option><option value="9">L9 Extreme</option>
      <option value="10">L10 Maximum</option><option value="11">L11 Paragon</option><option value="12" selected>L12 Ultimate</option>
    </select>
    <button class="theme-toggle" id="theme-btn" title="Toggle theme" aria-label="Toggle dark/light theme">☾</button>
    <span class="lang-badge" id="lang-badge">Auto</span>
    <span class="hdr-status" id="status-text">Ready</span>
  </div>
</header>

<div class="pipe" id="pipe-cont"></div>

<div class="undo-bar" style="margin:0 20px;padding:4px 8px">
  <button class="btn btn-s" id="undo-btn" disabled title="Undo (Ctrl+Z)" aria-label="Undo">↩ Undo</button>
  <button class="btn btn-s" id="redo-btn" disabled title="Redo (Ctrl+Y)" aria-label="Redo">↪ Redo</button>
  <span class="undo-label"><kbd class="kbd">Ctrl</kbd>+<kbd class="kbd">Z</kbd> / <kbd class="kbd">Y</kbd></span>
</div>

<div class="main">
<aside class="side">
  <div class="side-tabs">
    <div class="side-tab active" data-panel="p-tech">Techniques</div>
    <div class="side-tab" data-panel="p-lang">Languages</div>
    <div class="side-tab" data-panel="p-config">Config</div>
    <div class="side-tab" data-panel="p-adv">Advanced</div>
  </div>
  <div class="side-content">
    <!-- TECHNIQUES PANEL -->
    <div class="side-panel active" id="p-tech">
      <div class="search-box">
        <span class="search-icon">🔍</span>
        <input type="text" id="tech-search" placeholder="Search techniques..." aria-label="Search techniques">
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Code Transformation (3)</div>
        <div class="chkg">
          <label class="chk" data-cat="transform" data-tech="simple"><input type="checkbox"><span class="ck">✓</span>Var Rename</label>
          <label class="chk" data-cat="transform" data-tech="unicode"><input type="checkbox"><span class="ck">✓</span>Unicode Vars</label>
          <label class="chk" data-cat="transform" data-tech="integer"><input type="checkbox"><span class="ck">✓</span>Int Encode</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Dead Code Injection (4)</div>
        <div class="chkg">
          <label class="chk" data-cat="deadcode" data-tech="light"><input type="checkbox"><span class="ck">✓</span>Light</label>
          <label class="chk" data-cat="deadcode" data-tech="medium"><input type="checkbox"><span class="ck">✓</span>Medium</label>
          <label class="chk" data-cat="deadcode" data-tech="heavy"><input type="checkbox"><span class="ck">✓</span>Heavy</label>
          <label class="chk" data-cat="deadcode" data-tech="extreme"><input type="checkbox"><span class="ck">✓</span>Extreme</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Control Flow (2)</div>
        <div class="chkg">
          <label class="chk" data-cat="controlflow" data-tech="basic"><input type="checkbox"><span class="ck">✓</span>Basic Split</label>
          <label class="chk" data-cat="controlflow" data-tech="full"><input type="checkbox"><span class="ck">✓</span>Full Flatten</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Opaque Predicates (4)</div>
        <div class="chkg">
          <label class="chk" data-cat="opaque" data-tech="basic"><input type="checkbox"><span class="ck">✓</span>Basic</label>
          <label class="chk" data-cat="opaque" data-tech="medium"><input type="checkbox"><span class="ck">✓</span>Medium</label>
          <label class="chk" data-cat="opaque" data-tech="heavy"><input type="checkbox"><span class="ck">✓</span>Heavy</label>
          <label class="chk" data-cat="opaque" data-tech="extreme"><input type="checkbox"><span class="ck">✓</span>Extreme</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Code Virtualization (4)</div>
        <div class="chkg">
          <label class="chk" data-cat="codevir" data-tech="basic"><input type="checkbox"><span class="ck">✓</span>XOR VM</label>
          <label class="chk" data-cat="codevir" data-tech="bytecode"><input type="checkbox"><span class="ck">✓</span>Bytecode</label>
          <label class="chk" data-cat="codevir" data-tech="stack_vm"><input type="checkbox"><span class="ck">✓</span>Stack VM</label>
          <label class="chk" data-cat="codevir" data-tech="register_vm"><input type="checkbox"><span class="ck">✓</span>Register VM</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Instruction Substitution (4)</div>
        <div class="chkg">
          <label class="chk" data-cat="instruct" data-tech="basic"><input type="checkbox"><span class="ck">✓</span>Basic</label>
          <label class="chk" data-cat="instruct" data-tech="arithmetic"><input type="checkbox"><span class="ck">✓</span>Arithmetic</label>
          <label class="chk" data-cat="instruct" data-tech="string_split"><input type="checkbox"><span class="ck">✓</span>String Split</label>
          <label class="chk" data-cat="instruct" data-tech="full"><input type="checkbox"><span class="ck">✓</span>Full</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">String Encryption (7)</div>
        <div class="chkg">
          <label class="chk" data-cat="strencrypt" data-tech="xor"><input type="checkbox"><span class="ck">✓</span>XOR</label>
          <label class="chk" data-cat="strencrypt" data-tech="aes"><input type="checkbox"><span class="ck">✓</span>AES</label>
          <label class="chk" data-cat="strencrypt" data-tech="rc4"><input type="checkbox"><span class="ck">✓</span>RC4</label>
          <label class="chk" data-cat="strencrypt" data-tech="chacha"><input type="checkbox"><span class="ck">✓</span>ChaCha</label>
          <label class="chk" data-cat="strencrypt" data-tech="reverse"><input type="checkbox"><span class="ck">✓</span>Reverse</label>
          <label class="chk" data-cat="strencrypt" data-tech="caesar"><input type="checkbox"><span class="ck">✓</span>Caesar</label>
          <label class="chk" data-cat="strencrypt" data-tech="base64"><input type="checkbox"><span class="ck">✓</span>Base64</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Anti-AI / Anti-LLM (7)</div>
        <div class="chkg">
          <label class="chk" data-cat="antiai" data-tech="basic"><input type="checkbox"><span class="ck">✓</span>Basic</label>
          <label class="chk" data-cat="antiai" data-tech="confuse"><input type="checkbox"><span class="ck">✓</span>Confuse</label>
          <label class="chk" data-cat="antiai" data-tech="semantic_drift"><input type="checkbox"><span class="ck">✓</span>Semantic Drift</label>
          <label class="chk" data-cat="antiai" data-tech="noise_inject"><input type="checkbox"><span class="ck">✓</span>Noise Inject</label>
          <label class="chk" data-cat="antiai" data-tech="rename_entities"><input type="checkbox"><span class="ck">✓</span>Rename Entities</label>
          <label class="chk" data-cat="antiai" data-tech="structure_scramble"><input type="checkbox"><span class="ck">✓</span>Struct Scramble</label>
          <label class="chk" data-cat="antiai" data-tech="full"><input type="checkbox"><span class="ck">✓</span>Full Anti-AI</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Self-Modifying Code (4)</div>
        <div class="chkg">
          <label class="chk" data-cat="selfmod" data-tech="basic"><input type="checkbox"><span class="ck">✓</span>XOR Self-Mod</label>
          <label class="chk" data-cat="selfmod" data-tech="runtime"><input type="checkbox"><span class="ck">✓</span>Runtime Decomp</label>
          <label class="chk" data-cat="selfmod" data-tech="mutation"><input type="checkbox"><span class="ck">✓</span>Mutation</label>
          <label class="chk" data-cat="selfmod" data-tech="chameleon"><input type="checkbox"><span class="ck">✓</span>Chameleon</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Watermarking (6)</div>
        <div class="chkg">
          <label class="chk" data-cat="watermark" data-tech="basic"><input type="checkbox"><span class="ck">✓</span>Basic</label>
          <label class="chk" data-cat="watermark" data-tech="binary"><input type="checkbox"><span class="ck">✓</span>Binary</label>
          <label class="chk" data-cat="watermark" data-tech="invisible"><input type="checkbox"><span class="ck">✓</span>Invisible</label>
          <label class="chk" data-cat="watermark" data-tech="metadata"><input type="checkbox"><span class="ck">✓</span>Metadata</label>
          <label class="chk" data-cat="watermark" data-tech="hmac"><input type="checkbox"><span class="ck">✓</span>HMAC</label>
          <label class="chk" data-cat="watermark" data-tech="trace"><input type="checkbox"><span class="ck">✓</span>Trace</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Anti-Features (8)</div>
        <div class="chkg">
          <label class="chk" data-cat="anti" data-tech="debug"><input type="checkbox"><span class="ck">✓</span>Anti-Debug</label>
          <label class="chk" data-cat="anti" data-tech="tamper"><input type="checkbox"><span class="ck">✓</span>Anti-Tamper</label>
          <label class="chk" data-cat="anti" data-tech="vm"><input type="checkbox"><span class="ck">✓</span>VM Detect</label>
          <label class="chk" data-cat="anti" data-tech="timebomb"><input type="checkbox"><span class="ck">✓</span>Time Bomb</label>
          <label class="chk" data-cat="anti" data-tech="hwid"><input type="checkbox"><span class="ck">✓</span>HWID Lock</label>
          <label class="chk" data-cat="anti" data-tech="checksum"><input type="checkbox"><span class="ck">✓</span>Checksum</label>
          <label class="chk" data-cat="anti" data-tech="integrity"><input type="checkbox"><span class="ck">✓</span>Integrity</label>
          <label class="chk" data-cat="anti" data-tech="callback"><input type="checkbox"><span class="ck">✓</span>Callback</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Polymorphic</div>
        <div class="chkg">
          <label class="chk" data-cat="polymorphic" data-tech="scramble_comments"><input type="checkbox"><span class="ck">✓</span>Scramble Comments</label>
          <label class="chk" data-cat="polymorphic" data-tech="add_whitespace"><input type="checkbox"><span class="ck">✓</span>Add Whitespace</label>
          <label class="chk" data-cat="polymorphic" data-tech="swap_quotes"><input type="checkbox"><span class="ck">✓</span>Swap Quotes</label>
          <label class="chk" data-cat="polymorphic" data-tech="full"><input type="checkbox"><span class="ck">✓</span>Full Polymorphic</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Code Splitting</div>
        <div class="chkg">
          <label class="chk" data-cat="split" data-tech="basic"><input type="checkbox"><span class="ck">✓</span>3 Chunks</label>
          <label class="chk" data-cat="split" data-tech="heavy"><input type="checkbox"><span class="ck">✓</span>5 Chunks</label>
          <label class="chk" data-cat="split" data-tech="extreme"><input type="checkbox"><span class="ck">✓</span>8 Chunks</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Steganography</div>
        <div class="chkg">
          <label class="chk" data-cat="stegano" data-tech="text"><input type="checkbox"><span class="ck">✓</span>Text Hide</label>
          <label class="chk" data-cat="stegano" data-tech="html_comment"><input type="checkbox"><span class="ck">✓</span>HTML Comment</label>
          <label class="chk" data-cat="stegano" data-tech="zero_width"><input type="checkbox"><span class="ck">✓</span>Zero Width</label>
          <label class="chk" data-cat="stegano" data-tech="unicode_lookalike"><input type="checkbox"><span class="ck">✓</span>Unicode Lookalike</label>
          <label class="chk" data-cat="stegano" data-tech="whitespace"><input type="checkbox"><span class="ck">✓</span>Whitespace</label>
          <label class="chk" data-cat="stegano" data-tech="css_hidden"><input type="checkbox"><span class="ck">✓</span>CSS Hidden</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Code Encoding</div>
        <div class="chkg">
          <label class="chk" data-cat="codeenc" data-tech="base64"><input type="checkbox"><span class="ck">✓</span>Base64</label>
          <label class="chk" data-cat="codeenc" data-tech="hex"><input type="checkbox"><span class="ck">✓</span>Hex</label>
          <label class="chk" data-cat="codeenc" data-tech="oct"><input type="checkbox"><span class="ck">✓</span>Octal</label>
          <label class="chk" data-cat="codeenc" data-tech="bin"><input type="checkbox"><span class="ck">✓</span>Binary</label>
          <label class="chk" data-cat="codeenc" data-tech="rot13"><input type="checkbox"><span class="ck">✓</span>ROT13</label>
          <label class="chk" data-cat="codeenc" data-tech="rot47"><input type="checkbox"><span class="ck">✓</span>ROT47</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">String Array Obfuscation</div>
        <div class="chkg">
          <label class="chk" data-cat="strarray" data-tech="xor"><input type="checkbox"><span class="ck">✓</span>XOR Array</label>
          <label class="chk" data-cat="strarray" data-tech="aes"><input type="checkbox"><span class="ck">✓</span>AES Array</label>
          <label class="chk" data-cat="strarray" data-tech="base64"><input type="checkbox"><span class="ck">✓</span>Base64 Array</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">String Decomposition</div>
        <div class="chkg">
          <label class="chk" data-cat="decompose" data-tech="basic"><input type="checkbox"><span class="ck">✓</span>3 Chunks</label>
          <label class="chk" data-cat="decompose" data-tech="heavy"><input type="checkbox"><span class="ck">✓</span>5 Chunks</label>
          <label class="chk" data-cat="decompose" data-tech="extreme"><input type="checkbox"><span class="ck">✓</span>8 Chunks</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Runtime</div>
        <div class="chkg">
          <label class="chk" data-cat="autoimport" data-tech="auto"><input type="checkbox"><span class="ck">✓</span>Auto-Import Modules</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Wrapper / Packaging</div>
        <div class="chkg">
          <label class="chk" data-cat="wrapper" data-tech="stub"><input type="checkbox"><span class="ck">✓</span>Self-Execute</label>
          <label class="chk" data-cat="wrapper" data-tech="iife"><input type="checkbox"><span class="ck">✓</span>IIFE (JS)</label>
          <label class="chk" data-cat="wrapper" data-tech="gzinflate"><input type="checkbox"><span class="ck">✓</span>GZip (PHP)</label>
          <label class="chk" data-cat="wrapper" data-tech="base64"><input type="checkbox"><span class="ck">✓</span>Base64</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Special</div>
        <div class="chkg">
          <label class="chk" data-cat="marshal" data-tech="marshal"><input type="checkbox"><span class="ck">✓</span>Marshal (Py)</label>
        </div>
      </div>
    </div>

    <!-- LANGUAGES PANEL -->
    <div class="side-panel" id="p-lang">
      <div class="side-sec">
        <div class="side-sec-title">Detection</div>
        <div class="chkg" style="flex-direction:column;gap:2px">
          <label class="chk lang-opt active" style="width:100%" data-lang="auto"><input type="checkbox" checked><span class="ck">✓</span>Auto Detect</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Scripting</div>
        <div class="chkg" style="flex-direction:column;gap:2px">
          <label class="chk lang-opt" style="width:100%" data-lang="python"><input type="checkbox"><span class="ck">✓</span>Python</label>
          <label class="chk lang-opt" style="width:100%" data-lang="javascript"><input type="checkbox"><span class="ck">✓</span>JavaScript</label>
          <label class="chk lang-opt" style="width:100%" data-lang="typescript"><input type="checkbox"><span class="ck">✓</span>TypeScript</label>
          <label class="chk lang-opt" style="width:100%" data-lang="php"><input type="checkbox"><span class="ck">✓</span>PHP</label>
          <label class="chk lang-opt" style="width:100%" data-lang="ruby"><input type="checkbox"><span class="ck">✓</span>Ruby</label>
          <label class="chk lang-opt" style="width:100%" data-lang="perl"><input type="checkbox"><span class="ck">✓</span>Perl</label>
          <label class="chk lang-opt" style="width:100%" data-lang="lua"><input type="checkbox"><span class="ck">✓</span>Lua</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Compiled / Static</div>
        <div class="chkg" style="flex-direction:column;gap:2px">
          <label class="chk lang-opt" style="width:100%" data-lang="go"><input type="checkbox"><span class="ck">✓</span>Go</label>
          <label class="chk lang-opt" style="width:100%" data-lang="csharp"><input type="checkbox"><span class="ck">✓</span>C#</label>
          <label class="chk lang-opt" style="width:100%" data-lang="java"><input type="checkbox"><span class="ck">✓</span>Java</label>
          <label class="chk lang-opt" style="width:100%" data-lang="kotlin"><input type="checkbox"><span class="ck">✓</span>Kotlin</label>
          <label class="chk lang-opt" style="width:100%" data-lang="scala"><input type="checkbox"><span class="ck">✓</span>Scala</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Shell</div>
        <div class="chkg" style="flex-direction:column;gap:2px">
          <label class="chk lang-opt" style="width:100%" data-lang="bash"><input type="checkbox"><span class="ck">✓</span>Bash</label>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Best Algorithms</div>
        <div id="lang-algo-info" style="font-size:10px;color:var(--tx2);line-height:1.4">
          Select a language to see best obfuscation algorithms for it.
        </div>
      </div>
    </div>

    <!-- ADVANCED PANEL -->
    <div class="side-panel" id="p-adv">
      <div class="side-sec">
        <div class="side-sec-title">Nested Encryption</div>
        <p style="font-size:10px;color:var(--tx2);margin-bottom:8px">Encrypt the output multiple times. Each round re-obfuscates the previous output.</p>
        <div class="nest-row">
          <label>Round count</label>
          <input type="number" id="nest-count" min="1" max="5" value="1">
          <button class="btn btn-s" id="re-encrypt-btn">🔒 Re-Encrypt</button>
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Pipeline Configuration</div>
        <div id="adv-pipeline-list">
          <div class="adv-empty">Click steps in the pipeline bar to configure options</div>
        </div>
        <div style="display:flex;gap:4px;margin-top:8px">
          <button class="btn btn-s" id="export-pipeline-btn">Export Pipeline</button>
          <button class="btn btn-s" id="import-pipeline-btn">Import Pipeline</button>
          <input type="file" id="import-pipeline-input" style="display:none" accept=".json">
        </div>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Analysis</div>
        <button class="btn btn-s btn-f" id="analyze-btn">🔍 Analyze Code</button>
        <div id="analyze-panel" class="analyze-panel" style="display:none"></div>
      </div>
    </div>

    <!-- CONFIG PANEL -->
    <div class="side-panel" id="p-config">
      <div class="side-sec">
        <div class="side-sec-title">Language</div>
        <select class="sel" id="lang-sel">
          <option value="auto">Auto Detect</option>
          <optgroup label="Scripting">
            <option value="python">Python</option><option value="javascript">JavaScript</option>
            <option value="typescript">TypeScript</option><option value="php">PHP</option>
            <option value="ruby">Ruby</option><option value="perl">Perl</option>
            <option value="lua">Lua</option>
          </optgroup>
          <optgroup label="Compiled / Static">
            <option value="go">Go</option><option value="csharp">C#</option>
            <option value="java">Java</option><option value="kotlin">Kotlin</option>
            <option value="scala">Scala</option>
          </optgroup>
          <optgroup label="Shell">
            <option value="bash">Bash</option>
          </optgroup>
        </select>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Output Mode</div>
        <select class="sel" id="out-mode">
          <option value="auto">Auto (text/file)</option>
          <option value="text">Always Text</option>
          <option value="file">Always File</option>
        </select>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Output Wrapper</div>
        <select class="sel" id="wrapper-sel">
          <option value="raw">Raw Output</option>
          <option value="eval">eval()</option>
          <option value="exec">exec()</option>
          <option value="stub">Self-Executing Stub</option>
          <option value="function">Function() (JS)</option>
          <option value="marshal">marshal (Python)</option>
          <option value="base64">Base64 Wrapped</option>
          <option value="gzinflate">GZip+Base64 (PHP)</option>
          <option value="assert">assert() (PHP)</option>
          <option value="iife">IIFE (JS)</option>
          <option value="async_wrap">Async Wrap (JS)</option>
          <option value="instance_eval">instance_eval (Ruby)</option>
        </select>
      </div>
      <div class="side-sec">
        <div class="side-sec-title">Keyboard Shortcuts</div>
        <div style="font-size:10px;color:var(--tx2);line-height:1.8">
          <div><kbd class="kbd">Ctrl</kbd>+<kbd class="kbd">Enter</kbd> Build</div>
          <div><kbd class="kbd">Ctrl</kbd>+<kbd class="kbd">Z</kbd> Undo</div>
          <div><kbd class="kbd">Ctrl</kbd>+<kbd class="kbd">Y</kbd> Redo</div>
          <div><kbd class="kbd">Esc</kbd> Close modal</div>
          <div><kbd class="kbd">?</kbd> Show shortcuts</div>
        </div>
      </div>
    </div>
  </div>
</aside>

<main class="ed-panel">
  <div class="ed-toolbar">
    <div class="ed-tabs">
      <div class="ed-tab active" id="input-tab">Code</div>
      <div class="ed-tab" id="file-tab">File</div>
    </div>
    <div class="ed-actions">
      <button class="btn btn-s" id="clear-btn">Clear</button>
      <button class="btn btn-s btn-p" id="process-btn" title="Build (Ctrl+Enter)">▶ Build</button>
    </div>
  </div>
  <div class="ed-split">
    <div class="ed-pane">
      <div class="ed-pane-hdr"><span>Source Code</span><span class="badge" id="file-name"></span></div>
      <div id="input-content" style="display:flex;flex:1;flex-direction:column">
        <textarea class="ed-ta" id="input-code" placeholder="Paste your source code here...&#10;&#10;Or switch to File tab to upload.&#10;&#10;Supported: Python, JavaScript, TypeScript, PHP, Ruby, Perl, Lua, Go, C#, Java, Kotlin, Scala, Bash" spellcheck="false"></textarea>
      </div>
      <div id="file-content" style="display:none;flex:1">
        <div class="fzone" id="file-zone">
          <div class="fzone-inner">
            <div class="big">Drop files here</div>
            <div class="main">or click to browse</div>
            <div class="sub">Any language, any extension — auto-detects</div>
          </div>
          <input type="file" id="file-input" style="display:none" accept="*">
        </div>
      </div>
    </div>
    <div class="ed-pane">
      <div class="ed-pane-hdr"><span>Output</span><div class="ed-actions"><button class="btn btn-s" id="copy-btn">Copy</button><button class="btn btn-s" id="download-btn" style="display:inline-flex">Download</button></div></div>
      <pre class="ed-out" id="out-pane" style="display:none"></pre>
    </div>
  </div>
  <div class="stats">
    <div class="stat"><span class="sl">Lines</span><span class="sv" id="stat-lines">0</span></div>
    <div class="stat"><span class="sl">Input</span><span class="sv" id="stat-size">0 KB</span></div>
    <div class="stat"><span class="sl">Output</span><span class="sv" id="stat-out-size">-</span></div>
    <div class="stat"><span class="sl">Ratio</span><span class="sv" id="stat-ratio">-</span></div>
    <div class="stat"><span class="sl">Entropy</span><span class="sv" id="stat-entropy">-</span><div class="sb"><div class="sf" id="stat-entropy-bar" style="width:0%"></div></div></div>
    <div class="stat"><span class="sl">Steps</span><span class="sv" id="stat-steps">0</span></div>
    <div class="stat"><span class="sl">Strength</span><span class="sv" id="stat-strength">-</span><div class="sb"><div class="sf" id="stat-strength-bar" style="width:0%"></div></div></div>
  </div>
</main>
</div>
</div>

<div class="modal-bg" id="modal-bg">
  <div class="modal">
    <div class="modal-title" id="modal-title">Configure Step</div>
    <div class="modal-body" id="modal-body"></div>
    <div class="modal-foot">
      <button class="btn" id="modal-cancel">Cancel</button>
      <button class="btn btn-p" id="modal-confirm">Apply</button>
    </div>
  </div>
</div>

<div class="shortcuts-help" id="shortcuts-overlay">
  <div class="shortcuts-card">
    <div class="shortcuts-title">Keyboard Shortcuts</div>
    <div class="shortcut-row"><span class="shortcut-desc">Build / Process</span><div class="shortcut-keys"><kbd class="kbd">Ctrl</kbd>+<kbd class="kbd">Enter</kbd></div></div>
    <div class="shortcut-row"><span class="shortcut-desc">Undo</span><div class="shortcut-keys"><kbd class="kbd">Ctrl</kbd>+<kbd class="kbd">Z</kbd></div></div>
    <div class="shortcut-row"><span class="shortcut-desc">Redo</span><div class="shortcut-keys"><kbd class="kbd">Ctrl</kbd>+<kbd class="kbd">Y</kbd></div></div>
    <div class="shortcut-row"><span class="shortcut-desc">Close modal / overlay</span><div class="shortcut-keys"><kbd class="kbd">Esc</kbd></div></div>
    <div class="shortcut-row"><span class="shortcut-desc">Show this help</span><div class="shortcut-keys"><kbd class="kbd">?</kbd></div></div>
  </div>
</div>

<div class="notify" id="notify" role="status" aria-live="polite"></div>
<button class="side-toggle" aria-label="Toggle sidebar">☰</button>
<div class="side-overlay"></div>

<script src="assets/js/app.js"></script>
</body>
</html>
