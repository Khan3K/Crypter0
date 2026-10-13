(function(){
'use strict';

const TECH_OPTIONS = {
  codevir: {
    key_size: {label:'Key Size', type:'select', opts:[{v:'16',l:'16 bytes'},{v:'32',l:'32 bytes (default)'},{v:'64',l:'64 bytes'},{v:'128',l:'128 bytes'}]}
  },
  strencrypt: {
    cipher: {label:'Cipher', type:'select', opts:[{v:'xor',l:'XOR'},{v:'aes',l:'AES-256'},{v:'rc4',l:'RC4'},{v:'chacha',l:'ChaCha20'},{v:'reverse',l:'Reverse'},{v:'caesar',l:'Caesar'},{v:'base64',l:'Base64'},{v:'envkey',l:'Env-Keyed'}]},
    layers: {label:'Layers', type:'select', opts:[{v:'1',l:'1 layer'},{v:'2',l:'2 layers'},{v:'3',l:'3 layers'}]},
    custom_key: {label:'Custom Key', type:'text'},
    polymorphic: {label:'Polymorphic Stubs', type:'toggle'}
  },
  anti: {
    debug: {label:'Anti-Debug', type:'toggle'},
    tamper: {label:'Anti-Tamper', type:'toggle'},
    vm: {label:'VM Detect', type:'toggle'},
    timebomb: {label:'Time Bomb', type:'toggle'},
    hwid: {label:'HWID Lock', type:'toggle'},
    checksum: {label:'Checksum', type:'toggle'},
    integrity: {label:'Integrity', type:'toggle'},
    callback: {label:'Callback', type:'toggle'},
    callback_url: {label:'Callback URL', type:'text'}
  },
  watermark: {
    type: {label:'Type', type:'select', opts:[{v:'basic',l:'Basic'},{v:'binary',l:'Binary'},{v:'invisible',l:'Invisible'},{v:'metadata',l:'Metadata'},{v:'hmac',l:'HMAC'},{v:'trace',l:'Trace'}]},
    user_id: {label:'User ID', type:'text'},
    timestamp: {label:'Timestamp', type:'text'}
  },
  transform: {
    style: {label:'Style', type:'select', opts:[{v:'simple',l:'ASCII'},{v:'unicode',l:'Unicode/Greek'},{v:'full',l:'Full Rename'}]}
  },
  deadcode: {
    density: {label:'Density', type:'select', opts:[{v:'light',l:'Light'},{v:'medium',l:'Medium'},{v:'heavy',l:'Heavy'},{v:'extreme',l:'Extreme'}]}
  },
  opaque: {
    density: {label:'Density', type:'select', opts:[{v:'basic',l:'Basic (3)'},{v:'medium',l:'Medium (8)'},{v:'heavy',l:'Heavy (15)'},{v:'extreme',l:'Extreme (30)'}]}
  },
  controlflow: {
    mode: {label:'Mode', type:'select', opts:[{v:'basic',l:'Block Split'},{v:'full',l:'Block + Noise'},{v:'flatten',l:'Switch Flatten'}]},
    block_size: {label:'Min Block Size', type:'select', opts:[{v:'2',l:'2 lines'},{v:'4',l:'4 lines (default)'},{v:'6',l:'6 lines'},{v:'8',l:'8 lines'}]}
  },
  selfmod: {
    method: {label:'Method', type:'select', opts:[{v:'basic',l:'XOR Self-Mod'},{v:'runtime',l:'Runtime Decompile'},{v:'mutation',l:'Mutation'},{v:'chameleon',l:'Chameleon'}]}
  },
  antiai: {
    technique: {label:'Technique', type:'select', opts:[{v:'basic',l:'Basic'},{v:'confuse',l:'Comment Confusion'},{v:'noise_inject',l:'Noise Inject'},{v:'semantic_drift',l:'Semantic Drift'},{v:'rename_entities',l:'Rename Entities'},{v:'structure_scramble',l:'Structure Scramble'},{v:'full',l:'Full Mix'}]}
  },
  instruct: {},
  polymorphic: {
    mode: {label:'Mode', type:'select', opts:[{v:'scramble_comments',l:'Scramble Comments'},{v:'add_whitespace',l:'Add Whitespace'},{v:'swap_quotes',l:'Swap Quotes'},{v:'full',l:'Full Polymorphic'}]}
  },
  split: {
    method: {label:'Chunks', type:'select', opts:[{v:'basic',l:'3 Chunks'},{v:'heavy',l:'5 Chunks'},{v:'extreme',l:'8 Chunks'}]}
  },
  stegano: {
    mode: {label:'Mode', type:'select', opts:[{v:'text',l:'Text Hide'},{v:'html_comment',l:'HTML Comment'},{v:'zero_width',l:'Zero Width'},{v:'unicode_lookalike',l:'Unicode Lookalike'},{v:'whitespace',l:'Whitespace'},{v:'css_hidden',l:'CSS Hidden'}]}
  },
  codeenc: {
    method: {label:'Encoding', type:'select', opts:[{v:'base64',l:'Base64'},{v:'hex',l:'Hex'},{v:'oct',l:'Octal'},{v:'bin',l:'Binary'},{v:'rot13',l:'ROT13'},{v:'rot47',l:'ROT47'}]}
  },
  wrapper: {},
  marshal: {},
  strarray: {
    method: {label:'Method', type:'select', opts:[{v:'xor',l:'XOR'},{v:'aes',l:'AES'},{v:'base64',l:'Base64'}]},
    shuffle: {label:'Shuffle Order', type:'toggle'},
    min_length: {label:'Min String Length', type:'text'}
  },
  decompose: {
    method: {label:'Chunks', type:'select', opts:[{v:'basic',l:'3 Chunks'},{v:'heavy',l:'5 Chunks'},{v:'extreme',l:'8 Chunks'}]}
  },
  autoimport: {}
};

const LANG_AUTO_FEATURES = {
  python: {anti:['debug','tamper','vm'], marshal:true, wrapper:'marshal'},
  javascript: {anti:['debug','tamper'], wrapper:'iife'},
  php: {anti:['debug','tamper'], wrapper:'gzinflate'},
  ruby: {anti:['debug'], wrapper:'instance_eval'},
  go: {anti:['debug'], wrapper:'stub'},
  java: {anti:['debug'], wrapper:'stub'},
  csharp: {anti:['debug'], wrapper:'stub'},
  kotlin: {anti:['debug'], wrapper:'stub'},
  scala: {anti:['debug'], wrapper:'stub'},
  perl: {anti:['debug'], wrapper:'stub'},
  lua: {anti:['debug'], wrapper:'stub'},
  bash: {anti:['debug'], wrapper:'base64'},
  typescript: {anti:['debug','tamper'], wrapper:'iife'},
};

const LANG_ALGO_INFO = {
  python: '<b>Best Algo:</b> XOR string enc, Polymorphic whitespace, Marshal wrapper<br><b>Crypto:</b> AES-256, ChaCha20, RC4<br><b>VM:</b> XOR VM, Stack VM<br><b>Unique:</b> Self-modifying (mutation, chameleon)',
  javascript: '<b>Best Algo:</b> XOR string enc, IIFE wrapper, Polymorphic<br><b>Crypto:</b> AES-256, RC4, ChaCha20<br><b>VM:</b> XOR VM, Bytecode<br><b>Unique:</b> Async wrap, Function() eval',
  typescript: '<b>Best Algo:</b> Same as JavaScript<br><b>Crypto:</b> AES-256, RC4, ChaCha20<br><b>VM:</b> XOR VM, Bytecode',
  php: '<b>Best Algo:</b> GZip+Base64 wrapper, XOR string enc<br><b>Crypto:</b> AES-256, RC4, ChaCha20<br><b>VM:</b> XOR VM, Stack VM<br><b>Unique:</b> assert() wrapper',
  ruby: '<b>Best Algo:</b> instance_eval wrapper, XOR string enc<br><b>Crypto:</b> AES-256, RC4, ChaCha20<br><b>VM:</b> generic VM<br><b>Unique:</b> Lambda-based dead code',
  go: '<b>Best Algo:</b> Variable rename, Opaque predicates, XOR dead code<br><b>Crypto:</b> XOR, Base64 (inline)<br><b>VM:</b> None (no eval)<br><b>Unique:</b> Blank identifier _ for dead vars',
  csharp: '<b>Best Algo:</b> Variable rename, Opaque predicates, XOR dead code<br><b>Crypto:</b> XOR, Base64 (inline)<br><b>VM:</b> None (no eval)<br><b>Unique:</b> Func/Action dead code',
  java: '<b>Best Algo:</b> Variable rename, Opaque predicates, XOR dead code<br><b>Crypto:</b> XOR, Base64 (inline)<br><b>VM:</b> None (no eval)<br><b>Unique:</b> Stream API dead code',
  kotlin: '<b>Best Algo:</b> Variable rename, Opaque predicates, Lambda dead code<br><b>Crypto:</b> XOR, Base64 (inline)<br><b>VM:</b> None (no eval)<br><b>Unique:</b> also{} tricks, mapIndexed crypto',
  scala: '<b>Best Algo:</b> Variable rename, Opaque predicates, zipWithIndex crypto<br><b>Crypto:</b> XOR, Base64 (inline)<br><b>VM:</b> None (no eval)<br><b>Unique:</b> Lazy val dead code, pattern matching',
  perl: '<b>Best Algo:</b> Variable rename, MIME::Base64 wrapper<br><b>Crypto:</b> XOR, Base64<br><b>VM:</b> generic VM<br><b>Unique:</b> eval with MIME::Base64',
  lua: '<b>Best Algo:</b> Variable rename, load() wrapper<br><b>Crypto:</b> XOR (pure Lua), Base64<br><b>VM:</b> generic VM<br><b>Unique:</b> string.char byte array',
  bash: '<b>Best Algo:</b> Variable rename, base64 eval wrapper<br><b>Crypto:</b> Base64, XOR<br><b>VM:</b> None<br><b>Unique:</b> $(echo ... | base64 -d) pattern',
};

const LANG_TECH_COMPAT = {
  python:     ['transform','deadcode','controlflow','strencrypt','codevir','selfmod','instruct','opaque','antiai','watermark','polymorphic','split','stegano','codeenc','anti','marshal','wrapper','strarray','decompose','autoimport'],
  javascript: ['transform','deadcode','controlflow','strencrypt','codevir','selfmod','instruct','opaque','antiai','watermark','polymorphic','split','stegano','codeenc','anti','wrapper','strarray','decompose','autoimport'],
  typescript: ['transform','deadcode','controlflow','strencrypt','codevir','selfmod','instruct','opaque','antiai','watermark','polymorphic','split','stegano','codeenc','anti','wrapper','strarray','decompose','autoimport'],
  php:        ['transform','deadcode','controlflow','strencrypt','codevir','selfmod','instruct','opaque','antiai','watermark','polymorphic','split','stegano','codeenc','anti','wrapper','strarray','decompose'],
  ruby:       ['transform','deadcode','strencrypt','instruct','opaque','antiai','watermark','polymorphic','stegano','codeenc','anti','wrapper'],
  perl:       ['transform','deadcode','strencrypt','instruct','opaque','antiai','watermark','polymorphic','stegano','codeenc','anti','wrapper'],
  lua:        ['transform','deadcode','strencrypt','instruct','opaque','antiai','watermark','polymorphic','stegano','codeenc','anti','wrapper'],
  go:         ['transform','deadcode','controlflow','strencrypt','instruct','opaque','antiai','watermark','polymorphic','codeenc','anti','wrapper'],
  csharp:     ['transform','deadcode','controlflow','strencrypt','instruct','opaque','antiai','watermark','polymorphic','codeenc','anti','wrapper'],
  java:       ['transform','deadcode','controlflow','strencrypt','instruct','opaque','antiai','watermark','polymorphic','codeenc','anti','wrapper'],
  kotlin:     ['transform','deadcode','controlflow','strencrypt','instruct','opaque','antiai','watermark','polymorphic','codeenc','anti','wrapper'],
  scala:      ['transform','deadcode','controlflow','strencrypt','instruct','opaque','antiai','watermark','polymorphic','codeenc','anti','wrapper'],
  bash:       ['transform','deadcode','strencrypt','instruct','opaque','watermark','polymorphic','codeenc','anti','wrapper'],
  generic:    ['transform','deadcode','controlflow','strencrypt','instruct','opaque','antiai','watermark','polymorphic','split','stegano','codeenc','anti','wrapper','strarray','decompose'],
};

const LANG_STRENCRYPT_COMPAT = {
  python:     ['xor','aes','rc4','chacha','reverse','caesar','base64'],
  javascript: ['xor','aes','rc4','chacha','reverse','caesar','base64'],
  typescript: ['xor','aes','rc4','chacha','reverse','caesar','base64'],
  php:        ['xor','aes','rc4','chacha','reverse','caesar','base64'],
  ruby:       ['xor','aes','rc4','chacha','reverse','caesar','base64'],
  perl:       ['xor','reverse','caesar','base64'],
  lua:        ['xor','reverse','base64'],
  go:         ['xor','reverse','caesar','base64'],
  csharp:     ['xor','reverse','base64'],
  java:       ['xor','reverse','base64'],
  kotlin:     ['xor','reverse','base64'],
  scala:      ['xor','reverse','base64'],
  bash:       ['xor','reverse','base64'],
  generic:    ['xor','reverse','base64'],
};

const CAT_LABELS = {
  transform:'TRF',deadcode:'DEAD',controlflow:'CF',anti:'SEC',marshal:'MRSH',
  wrapper:'W',opaque:'OPQ',codevir:'VM',selfmod:'SM',antiai:'AI',watermark:'WM',
  strencrypt:'SE',instruct:'IS',polymorphic:'POLY',split:'SPLIT',stegano:'STG',codeenc:'ENC',
  strarray:'SARR',decompose:'DCMP',autoimport:'AIMP'
};

const CAT_COLORS = {
  transform:'#6c6cff',deadcode:'#ffaa44',controlflow:'#ff4466',
  anti:'#ff44aa',marshal:'#44ddaa',wrapper:'#8888ff',opaque:'#aa66ff',codevir:'#ff6644',
  selfmod:'#ff88aa',antiai:'#aa88ff',watermark:'#ffaa88',strencrypt:'#44ffaa',
  instruct:'#88aaff',polymorphic:'#cc88ff',split:'#ff88cc',stegano:'#44aaff',codeenc:'#00d4aa',
  strarray:'#ff6688',decompose:'#88ddff',autoimport:'#44cc88'
};

const LANG_LABELS = {python:'Python',javascript:'JS',typescript:'TS',php:'PHP',ruby:'Ruby',perl:'Perl',lua:'Lua',go:'Go',csharp:'C#',java:'Java',kotlin:'Kotlin',scala:'Scala',bash:'Bash'};

function escHtml(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')}

const A = {
  state: {
    code:'', language:'auto', filename:'', pipeline:[], outputCode:'',
    processing:false, stats:{original_size:0,final_size:0,expansion_ratio:1,entropy:0,steps:[],language:''},
    stepN:0, _langManual:false
  },
  history: { past:[], future:[] },
  _searchQuery:'',
  el: {},

  init() {
    this.cache();
    this.bind();
    this.addDefaults();
    this.renderPipe();
    this.updateStats();
    this.autoLang();
    this.dragDrop();
    this.initSearch();
    this.initTheme();
    this.initKeyboardShortcuts();
    this.initAutoSave();
    this.initAnalyze();
    this.initMobileSidebar();
    setTimeout(() => this.updateTechCompat(), 100);
  },

  cache() {
    const $=id=>document.getElementById(id),qa=s=>document.querySelectorAll(s);
    this.el = {
      inputCode:$('input-code'), outPane:$('out-pane'), pipeCont:$('pipe-cont'),
      langSel:$('lang-sel'), outMode:$('out-mode'),
      processBtn:$('process-btn'), clearBtn:$('clear-btn'), copyBtn:$('copy-btn'),
      downloadBtn:$('download-btn'), fileInput:$('file-input'), fileZone:$('file-zone'),
      fileName:$('file-name'),
      statLines:$('stat-lines'), statSize:$('stat-size'), statOutSize:$('stat-out-size'),
      statRatio:$('stat-ratio'), statEntropy:$('stat-entropy'), statEntropyBar:$('stat-entropy-bar'),
      statStrength:$('stat-strength'), statStrengthBar:$('stat-strength-bar'), statSteps:$('stat-steps'),
      inputTab:$('input-tab'), fileTab:$('file-tab'),
      inputContent:$('input-content'), fileContent:$('file-content'),
      sideTabs:qa('.side-tab'), sidePanels:qa('.side-panel'),
      modalBg:$('modal-bg'), modalBody:$('modal-body'),
      modalTitle:$('modal-title'), modalConfirm:$('modal-confirm'), modalCancel:$('modal-cancel'),
      notify:$('notify'), statusText:$('status-text'),
      wrapperSel:$('wrapper-sel'), protectLevel:$('protect-level'),
      nestCount:$('nest-count'), reEncryptBtn:$('re-encrypt-btn'),
      searchInput:$('tech-search'), undoBtn:$('undo-btn'), redoBtn:$('redo-btn'),
      analyzeBtn:$('analyze-btn'), analyzePanel:$('analyze-panel'),
      themeBtn:$('theme-btn'), shortcutsOverlay:$('shortcuts-overlay')
    };
  },

  bind() {
    const e=this.el;
    e.processBtn.addEventListener('click',()=>this.process());
    e.clearBtn.addEventListener('click',()=>this.clearAll());
    e.copyBtn.addEventListener('click',()=>this.copyOut());
    e.downloadBtn.addEventListener('click',()=>this.downloadOut());
    e.langSel.addEventListener('change',ev=>{
      this.state.language=ev.target.value;
      this.state._langManual=(ev.target.value!=='auto');
      this.syncLangPills(ev.target.value);
      this.updateLangBadge();this.updateLangAlgoInfo();this.updateTechCompat();
      this.autoAddFeatures(this.el.protectLevel);
    });
    e.inputCode.addEventListener('input',ev=>{
      this.state.code=ev.target.value;
      this.updateStats();
      if(!this.state._langManual)this.autoLang();
    });
    e.fileInput.addEventListener('change',ev=>this.handleFile(ev));
    e.inputTab.addEventListener('click',()=>this.switchInput('code'));
    e.fileTab.addEventListener('click',()=>this.switchInput('file'));

    document.querySelectorAll('.lang-opt').forEach(opt=>{
      opt.addEventListener('click',()=>{
        document.querySelectorAll('.lang-opt').forEach(x=>x.classList.remove('active'));
        opt.classList.add('active');
        const lang=opt.dataset.lang;
        this.state.language=lang;
        this.state._langManual=(lang!=='auto');
        this.el.langSel.value=lang;
        this.updateLangBadge();this.updateLangAlgoInfo();this.updateTechCompat();
        this.autoAddFeatures(this.el.protectLevel);
      });
    });

    e.sideTabs.forEach(t=>{t.addEventListener('click',()=>{
      e.sideTabs.forEach(x=>x.classList.remove('active'));
      e.sidePanels.forEach(x=>x.classList.remove('active'));
      t.classList.add('active');
      const p=document.getElementById(t.dataset.panel);if(p)p.classList.add('active');
    })});

    e.modalCancel.addEventListener('click',()=>this.closeModal());
    e.modalBg.addEventListener('click',ev=>{if(ev.target===e.modalBg)this.closeModal()});

    if(e.protectLevel)e.protectLevel.addEventListener('change',()=>{
      this.loadLevel(parseInt(e.protectLevel.value));
    });
    if(e.wrapperSel)e.wrapperSel.addEventListener('change',()=>{
      const i=this.state.pipeline.findIndex(s=>s.cat==='wrapper');
      if(i>-1)this.state.pipeline.splice(i,1);
      if(e.wrapperSel.value!=='raw')this.state.pipeline.push({cat:'wrapper',tech:e.wrapperSel.value,opts:{}});
      this.renderPipe();
    });
    if(e.reEncryptBtn)e.reEncryptBtn.addEventListener('click',()=>this.reEncrypt());

    const exportBtn=document.getElementById('export-pipeline-btn');
    const importBtn=document.getElementById('import-pipeline-btn');
    const importInput=document.getElementById('import-pipeline-input');
    if(exportBtn)exportBtn.addEventListener('click',()=>this.exportPipeline());
    if(importBtn)importBtn.addEventListener('click',()=>importInput.click());
    if(importInput)importInput.addEventListener('change',ev=>{
      if(ev.target.files[0])this.importPipeline(ev.target.files[0]);
      ev.target.value='';
    });

    if(e.undoBtn)e.undoBtn.addEventListener('click',()=>this.undo());
    if(e.redoBtn)e.redoBtn.addEventListener('click',()=>this.redo());

    document.querySelectorAll('.chk[data-cat]').forEach(el=>{
      el.addEventListener('click',ev=>{
        if(ev.target.closest('.ck')||ev.target.tagName==='INPUT')return;
        const cb=this.querySelectorInput(el);
        cb.checked=!cb.checked;
        el.classList.toggle('on',cb.checked);
        const cat=el.dataset.cat,tech=el.dataset.tech;
        if(cb.checked){
          const step={cat,tech,opts:{}};
          this.pushHistory();
          this.state.pipeline.push(step);
          this.renderPipe();
          this.openStepCfg(this.state.pipeline.length-1);
        } else {
          this.pushHistory();
          for(let i=this.state.pipeline.length-1;i>=0;i--){
            if(this.state.pipeline[i].cat===cat&&this.state.pipeline[i].tech===tech){
              this.state.pipeline.splice(i,1);break;
            }
          }
          this.renderPipe();
        }
      });
    });

    document.querySelectorAll('.chk[data-lang]').forEach(el=>{
      el.addEventListener('click',ev=>{
        if(ev.target.closest('.ck')||ev.target.tagName==='INPUT')return;
        const cb=this.querySelectorInput(el);
        cb.checked=!cb.checked;
        el.classList.toggle('on',cb.checked);
        if(cb.checked){
          this.state.language=el.dataset.lang;
          this.state._langManual=(el.dataset.lang!=='auto');
          document.getElementById('lang-sel').value=el.dataset.lang;
          this.updateLangBadge();this.updateLangAlgoInfo();this.updateTechCompat();
          this.autoAddFeatures(this.el.protectLevel);
        }
      });
    });
  },

  querySelectorInput(el){return el.querySelector('input[type="checkbox"]')},

  addDefaults(){
    this.state.pipeline=[
      {cat:'transform',tech:'simple',opts:{style:'simple'}},
      {cat:'deadcode',tech:'medium',opts:{density:'medium'}},
      {cat:'opaque',tech:'basic',opts:{density:'basic'}},
      {cat:'instruct',tech:'basic',opts:{}},
      {cat:'strencrypt',tech:'xor',opts:{cipher:'xor',layers:'1'}}
    ];
    this.state.stepN=5;
  },

  pushHistory(){
    this.history.past.push(JSON.parse(JSON.stringify(this.state.pipeline)));
    if(this.history.past.length>50)this.history.past.shift();
    this.history.future=[];
    this.updateUndoRedoUI();
  },

  undo(){
    if(!this.history.past.length)return;
    this.history.future.push(JSON.parse(JSON.stringify(this.state.pipeline)));
    this.state.pipeline=this.history.past.pop();
    this.renderPipe();
    this.updateUndoRedoUI();
    this.notify('Undo','info');
  },

  redo(){
    if(!this.history.future.length)return;
    this.history.past.push(JSON.parse(JSON.stringify(this.state.pipeline)));
    this.state.pipeline=this.history.future.pop();
    this.renderPipe();
    this.updateUndoRedoUI();
    this.notify('Redo','info');
  },

  updateUndoRedoUI(){
    if(this.el.undoBtn)this.el.undoBtn.disabled=!this.history.past.length;
    if(this.el.redoBtn)this.el.redoBtn.disabled=!this.history.future.length;
  },

  renderPipe(){
    const c=this.el.pipeCont;c.innerHTML='';
    const s=this.state.pipeline;
    const query=this._searchQuery.toLowerCase();
    s.forEach((step,i)=>{
      const label=(CAT_LABELS[step.cat]||step.cat);
      const name=step.tech;
      const matchesSearch=!query||
        label.toLowerCase().includes(query)||
        name.toLowerCase().includes(query)||
        (step.cat||'').toLowerCase().includes(query);
      if(i>0){const a=document.createElement('span');a.className='pipe-arrow';a.textContent='→';c.appendChild(a)}
      const el=document.createElement('div');el.className='pipe-step';el.draggable=true;el.dataset.index=i;
      if(!matchesSearch&&query)el.classList.add('dimmed');
      const compat=LANG_TECH_COMPAT[this.state.language]||LANG_TECH_COMPAT.generic;
      const isUnsupported=!compat.includes(step.cat);
      if(isUnsupported)el.classList.add('unsupported');
      const optCount=step.opts&&Object.keys(step.opts).length?` <span class="opt-badge">${Object.keys(step.opts).length}</span>`:'';
      el.innerHTML=`<span class="sn" style="background:${CAT_COLORS[step.cat]||'#555'}">${i+1}</span><span class="sc">${escHtml(label)}</span><span class="st">${escHtml(step.tech)}${optCount}${isUnsupported?' ⚠':''}</span><button class="sr" data-index="${i}" aria-label="Remove step ${i+1}">✕</button>`;
      if(isUnsupported)el.title='This technique may not work for '+this.state.language;
      el.addEventListener('click',ev=>{if(!ev.target.closest('.sr'))this.openStepCfg(i)});
      el.querySelector('.sr').addEventListener('click',ev=>{ev.stopPropagation();this.pushHistory();this.removeStep(i)});
      el.addEventListener('dragstart',()=>el.classList.add('dragging'));
      el.addEventListener('dragend',()=>el.classList.remove('dragging'));
      el.addEventListener('dragover',ev=>{ev.preventDefault();el.classList.add('over')});
      el.addEventListener('dragleave',()=>el.classList.remove('over'));
      el.addEventListener('drop',ev=>{ev.preventDefault();el.classList.remove('over');
        const from=parseInt(document.querySelector('.pipe-step.dragging')?.dataset?.index);
        const to=parseInt(el.dataset.index);
        if(!isNaN(from)&&!isNaN(to)&&from!==to){this.pushHistory();this.moveStep(from,to)}
      });
      c.appendChild(el);
    });
    const add=document.createElement('button');add.className='pipe-add';add.innerHTML='+ Add Step';
    add.addEventListener('click',()=>this.openAddStep());c.appendChild(add);
    this.renderAdvPanel();this.updateStats();
  },

  addStep(cat,tech,opts){
    this.pushHistory();
    this.state.pipeline.push({cat,tech,opts:opts||{}});
    this.state.stepN++;
    this.renderPipe();
  },

  removeStep(i){
    if(this.state.pipeline.length<=1)return;
    this.state.pipeline.splice(i,1);
    this.renderPipe();
  },

  moveStep(f,t){
    const item=this.state.pipeline.splice(f,1)[0];
    this.state.pipeline.splice(t,0,item);
    this.renderPipe();
  },

  renderAdvPanel(){
    const container=document.getElementById('adv-pipeline-list');
    if(!container)return;
    const s=this.state.pipeline;
    if(!s.length){container.innerHTML='<div class="adv-empty">No steps in pipeline</div>';return}
    let html='';
    s.forEach((step,i)=>{
      const opts=step.opts||{};
      const optKeys=Object.keys(opts);
      const optStr=optKeys.length?optKeys.map(k=>{const v=opts[k];return typeof v==='boolean'?(v?k:''):k+'='+v}).filter(Boolean).join(', '):'no options';
      html+=`<div class="adv-step" data-index="${i}"><span class="asn">${i+1}</span><span class="asc">${escHtml(CAT_LABELS[step.cat]||step.cat)}</span><span class="ast">${escHtml(step.tech)} <span class="aopt">${escHtml(optStr)}</span></span></div>`;
    });
    container.innerHTML=html;
    container.querySelectorAll('.adv-step').forEach(el=>{
      el.addEventListener('click',()=>{this.openStepCfg(parseInt(el.dataset.index))});
    });
  },

  openStepCfg(i){
    const step=i<this.state.pipeline.length?this.state.pipeline[i]:{cat:'codeenc',tech:'base64',opts:{}};
    const isNew=i>=this.state.pipeline.length;
    this.el.modalTitle.textContent=isNew?'Add Step':'Step '+(i+1)+': Configure';
    const allCats=['transform','deadcode','controlflow','instruct','strencrypt','opaque','codevir','selfmod','antiai','watermark','wrapper','marshal','polymorphic','split','stegano','codeenc','anti','strarray','decompose','autoimport'];
    const techOpts={
      transform:['simple','unicode','integer'],deadcode:['light','medium','heavy','extreme'],
      controlflow:['basic','full','flatten'],anti:['debug','tamper','vm','timebomb','hwid','checksum','callback','integrity'],
      instruct:['basic','arithmetic','string_split','full'],
      strencrypt:['xor','aes','rc4','chacha','reverse','caesar','base64','envkey'],
      opaque:['basic','medium','heavy','extreme'],codevir:['basic','bytecode','stack_vm','register_vm'],
      selfmod:['basic','runtime','mutation','chameleon'],
      antiai:['basic','confuse','noise_inject','semantic_drift','rename_entities','structure_scramble','full'],
      watermark:['basic','binary','invisible','metadata','hmac','trace'],
      wrapper:['raw','eval','exec','stub','function','assert','base64','gzinflate','iife','async_wrap','instance_eval'],
      marshal:['marshal'],polymorphic:['scramble_comments','add_whitespace','swap_quotes','full'],
      split:['basic','heavy','extreme'],stegano:['text','html_comment','zero_width','unicode_lookalike','whitespace','css_hidden'],
      codeenc:['base64','hex','oct','bin','rot13','rot47'],
      strarray:['xor','aes','base64'],decompose:['basic','heavy','extreme']
    };

    let html=`<div class="modal-grid">`;
    html+=`<div class="full"><label class="side-label">Category</label><select class="sel step-cat">${allCats.map(c=>`<option value="${c}" ${c===step.cat?'selected':''}>${c}</option>`).join('')}</select></div>`;
    html+=`<div class="full"><label class="side-label">Technique</label><select class="sel step-tech">${(techOpts[step.cat]||[]).map(t=>`<option value="${t}" ${t===step.tech?'selected':''}>${t}</option>`).join('')}</select></div>`;

    const opts=step.opts||{};
    const techDef=TECH_OPTIONS[step.cat]||{};
    const keys=Object.keys(techDef);
    if(keys.length>0){
      html+=`<div class="full modal-adv-title">Advanced Options</div>`;
      keys.forEach(k=>{
        const def=techDef[k];
        if(def.type==='select'){
          html+=`<div class="full"><label class="side-label">${escHtml(def.label)}</label><select class="sel step-opt" data-opt="${escHtml(k)}">${def.opts.map(o=>`<option value="${escHtml(o.v)}" ${(opts[k]||'')===o.v?'selected':''}>${escHtml(o.l)}</option>`).join('')}</select></div>`;
        } else if(def.type==='toggle'){
          html+=`<div><label class="chk step-opt-toggle" data-opt="${escHtml(k)}"><input type="checkbox" ${opts[k]?'checked':''}><span class="ck">✓</span>${escHtml(def.label)}</label></div>`;
        } else if(def.type==='text'){
          html+=`<div class="full"><label class="side-label">${escHtml(def.label)}</label><input class="inp step-opt" data-opt="${escHtml(k)}" value="${escHtml(opts[k]||'')}" placeholder="Random if empty"></div>`;
        }
      });
    }
    html+=`</div>`;
    this.el.modalBody.innerHTML=html;

    const catS=this.el.modalBody.querySelector('.step-cat');
    const techS=this.el.modalBody.querySelector('.step-tech');
    catS.addEventListener('change',()=>{
      const t=techOpts[catS.value]||[];
      techS.innerHTML=t.map(x=>`<option value="${x}">${x}</option>`).join('');
      const currentOpts={};
      this.el.modalBody.querySelectorAll('.step-opt').forEach(el=>{currentOpts[el.dataset.opt]=el.value});
      this.el.modalBody.querySelectorAll('.step-opt-toggle').forEach(el=>{currentOpts[el.dataset.opt]=el.querySelector('input').checked});
      this.refreshModalOpts(catS.value,currentOpts);
    });

    this.el.modalConfirm.onclick=()=>{
      const newOpts={};
      this.el.modalBody.querySelectorAll('.step-opt').forEach(el=>{
        newOpts[el.dataset.opt]=el.value;
      });
      this.el.modalBody.querySelectorAll('.step-opt-toggle').forEach(el=>{
        newOpts[el.dataset.opt]=el.querySelector('input').checked;
      });
      if(isNew){this.addStep(catS.value,techS.value,newOpts)}
      else{this.pushHistory();step.cat=catS.value;step.tech=techS.value;step.opts=newOpts;this.renderPipe()}
      this.closeModal();
    };
    this.el.modalBg.classList.add('active');
  },

  refreshModalOpts(cat,currentOpts){
    const grid=this.el.modalBody.querySelector('.modal-grid');
    if(!grid)return;
    const existing=grid.querySelector('.modal-adv-title');
    if(existing){
      const toRemove=[];
      let sibling=existing.nextElementSibling;
      while(sibling){toRemove.push(sibling);sibling=sibling.nextElementSibling}
      toRemove.forEach(n=>n.remove());
      existing.remove();
    }
    const techDef=TECH_OPTIONS[cat]||{};
    const keys=Object.keys(techDef);
    if(keys.length===0)return;
    const title=document.createElement('div');title.className='full modal-adv-title';title.textContent='Advanced Options';
    grid.appendChild(title);
    keys.forEach(k=>{
      const def=techDef[k];
      if(def.type==='select'){
        const wrap=document.createElement('div');wrap.className='full';
        wrap.innerHTML=`<label class="side-label">${escHtml(def.label)}</label><select class="sel step-opt" data-opt="${escHtml(k)}">${def.opts.map(o=>`<option value="${escHtml(o.v)}" ${(currentOpts[k]||'')===o.v?'selected':''}>${escHtml(o.l)}</option>`).join('')}</select>`;
        grid.appendChild(wrap);
      } else if(def.type==='toggle'){
        const wrap=document.createElement('div');
        wrap.innerHTML=`<label class="chk step-opt-toggle" data-opt="${escHtml(k)}"><input type="checkbox" ${currentOpts[k]?'checked':''}><span class="ck">✓</span>${escHtml(def.label)}</label>`;
        grid.appendChild(wrap);
      } else if(def.type==='text'){
        const wrap=document.createElement('div');wrap.className='full';
        wrap.innerHTML=`<label class="side-label">${escHtml(def.label)}</label><input class="inp step-opt" data-opt="${escHtml(k)}" value="${escHtml(currentOpts[k]||'')}" placeholder="Random if empty">`;
        grid.appendChild(wrap);
      }
    });
  },

  openAddStep(){this.openStepCfg(this.state.pipeline.length)},
  closeModal(){this.el.modalBg.classList.remove('active')},

  reEncrypt(){
    if(!this.state.outputCode){this.notify('No output to re-encrypt','error');return}
    this.state.code=this.state.outputCode;
    this.el.inputCode.value=this.state.code;
    this.autoLang();this.updateStats();
    this.process(true);
  },

  async process(isReEncrypt=false){
    if(this.state.processing)return;
    const code=this.state.code;
    if(!code){this.notify('Enter or upload code first','error');return}
    this.state.processing=true;
    const e=this.el;
    e.processBtn.disabled=true;e.processBtn.textContent='Processing...';e.processBtn.classList.add('processing');
    e.statusText.textContent='Processing...';
    try{
      const fd=new FormData();
      fd.append('code',code);fd.append('lang',this.state.language);
      fd.append('filename',this.state.filename);
      fd.append('output_mode',e.outMode.value);
      fd.append('pipeline',JSON.stringify(this.state.pipeline));
      fd.append('level',e.protectLevel?e.protectLevel.value:'12');
      if(e.nestCount)fd.append('nest',e.nestCount.value||'1');
      const resp=await fetch('api/process.php',{method:'POST',body:fd});
      if(!resp.ok){const txt=await resp.text();this.notify('Server error ('+resp.status+'): '+txt.substring(0,120),'error');return}
      const data=await resp.json();
      if(!data.success){this.notify(data.error||'Processing failed','error');return}
      this.state.outputCode=data.code;
      this.state.stats={original_size:data.original_size,final_size:data.final_size,expansion_ratio:data.expansion_ratio,entropy:data.entropy,steps:data.steps||[],language:data.language};
      e.outPane.textContent=data.code;e.outPane.style.display='block';
      e.downloadBtn.style.display=data.download?'inline-flex':'none';
      this.updateStats();
      let notifyMsg=`Crypted: ${(data.expansion_ratio*100).toFixed(0)}% | Entropy: ${data.entropy.toFixed(3)} | Steps: ${data.steps.length}`;
      if(data.warnings&&data.warnings.length){
        notifyMsg+=' | Warnings: '+data.warnings.length;
        this.notify(notifyMsg,'warning');
      } else {
        this.notify(notifyMsg,'success');
      }
      e.statusText.textContent='Ready — '+data.language;
      this.saveState();
    }catch(err){this.notify('Error: '+err.message,'error');e.statusText.textContent='Error'}
    finally{this.state.processing=false;e.processBtn.disabled=false;e.processBtn.textContent='▶ Build';e.processBtn.classList.remove('processing')}
  },

  clearAll(){
    this.pushHistory();
    this.state.code='';this.state.outputCode='';this.state.filename='';
    this.el.inputCode.value='';this.el.outPane.textContent='';this.el.outPane.style.display='none';
    this.el.downloadBtn.style.display='none';this.el.fileName.textContent='';
    this.state.stats={original_size:0,final_size:0,expansion_ratio:1,entropy:0,steps:[]};
    this.updateStats();this.el.statusText.textContent='Ready';
  },

  copyOut(){
    if(!this.state.outputCode)return;
    const code=this.state.outputCode;
    if(navigator.clipboard&&window.isSecureContext){
      navigator.clipboard.writeText(code).then(()=>this.notify('Copied','success')).catch(()=>this._copyFallback(code));
    }else{
      this._copyFallback(code);
    }
  },
  _copyFallback(code){
    const ta=document.createElement('textarea');
    ta.value=code;
    ta.style.cssText='position:fixed;left:-9999px;top:-9999px;opacity:0';
    document.body.appendChild(ta);
    ta.focus();ta.select();
    try{document.execCommand('copy');this.notify('Copied','success')}
    catch(e){
      if(navigator.share){navigator.share({text:code}).then(()=>this.notify('Shared','success')).catch(()=>this.notify('Copy failed','error'))}
      else{this.notify('Copy failed — select output manually','error')}
    }
    document.body.removeChild(ta);
  },

  downloadOut(){
    if(!this.state.outputCode)return;
    const f=document.createElement('form');f.method='POST';f.action='api/download.php';
    [['code',this.state.outputCode],['lang',this.state.stats.language||this.state.language],['filename',this.state.filename]].forEach(([k,v])=>{
      const i=document.createElement('input');i.type='hidden';i.name=k;i.value=v;f.appendChild(i);
    });
    document.body.appendChild(f);f.submit();document.body.removeChild(f);
  },

  handleFile(ev){const file=ev.target.files[0];if(file)this.readFile(file)},
  readFile(file){
    const r=new FileReader();r.onload=ev=>{
      this.state.code=ev.target.result;this.state.filename=file.name;
      this.el.inputCode.value=this.state.code;
      this.el.fileName.textContent=file.name+' ('+(file.size/1024).toFixed(1)+' KB)';
      this.autoLang();this.updateStats();this.notify('Loaded '+file.name,'success');
    };r.readAsText(file);
  },

  dragDrop(){
    const z=this.el.fileZone;if(!z)return;
    z.addEventListener('dragover',ev=>{ev.preventDefault();z.classList.add('dragover')});
    z.addEventListener('dragleave',()=>z.classList.remove('dragover'));
    z.addEventListener('drop',ev=>{ev.preventDefault();z.classList.remove('dragover');
      const file=ev.dataTransfer.files[0];if(file)this.readFile(file);
    });
    z.addEventListener('click',()=>this.el.fileInput.click());
  },

  switchInput(mode){
    this.el.inputTab.classList.toggle('active',mode==='code');
    this.el.fileTab.classList.toggle('active',mode==='file');
    this.el.inputContent.style.display=mode==='code'?'flex':'none';
    this.el.fileContent.style.display=mode==='file'?'flex':'none';
  },

  autoLang(){
    const c=this.state.code;if(!c)return;let l='generic';
    if(/^\s*(import|from)\s+\w+|def\s+\w+|class\s+\w+[\s:(]|print\s*\(|__name__/m.test(c))l='python';
    else if(/^\s*(const|let|var|interface|type|enum)\s+\w+.*:\s*(string|number|boolean)|import\s+type\s|m\.ts$/m.test(c))l='typescript';
    else if(/^\s*(const|let|var)\s+\w+|function\s+\w+|console\.\w+|require\s*\(|module\.exports|import\s+.*from|export\s+(default|const)/m.test(c))l='javascript';
    else if(/^\s*<\?php|namespace\s+\w+|use\s+[\w\\]+|function\s+\w+\s*\([^)]*\)\s*:\s*\w|\$[a-zA-Z_]/.test(c))l='php';
    else if(/^\s*def\s+\w+|class\s+\w+\s*(<|implements)|require\s|puts\s|attr_accessor|module\s+\w+/mi.test(c))l='ruby';
    else if(/^\s*use\s+\w+|my\s+\$|sub\s+\w+\s*\{|package\s+\w+/m.test(c))l='perl';
    else if(/^#!\s*\/bin\/(ba)?sh|^\s*export\s+\w+=|^\s*\$\{?\w+|^\s*function\s+\w+\s*\{/m.test(c))l='bash';
    else if(/^\s*local\s+\w+|^\s*function\s+\w+\s*\(|require\s*["']?\w+/m.test(c))l='lua';
    else if(/^\s*package\s+main|^\s*import\s*\(|^\s*func\s+\w+\s*\(|^\s*fmt\.\w+/m.test(c))l='go';
    else if(/^\s*using\s+System|^\s*namespace\s+\w+|^\s*(public|private)\s+(static\s+)?class/m.test(c))l='csharp';
    else if(/^\s*public\s+class\s+\w+|^\s*import\s+java\.|^\s*public\s+static\s+void\s+main/m.test(c))l='java';
    else if(/^\s*fun\s+\w+|^\s*val\s+\w+|^\s*class\s+\w+.*\(|^\s*object\s+\w+/m.test(c))l='kotlin';
    else if(/^\s*(val|var)\s+\w+\s*:|^\s*def\s+\w+|^\s*class\s+\w+.*\(|^\s*trait\s+\w+/mi.test(c))l='scala';
    this.el.langSel.value=l;this.state.language=l;
    this.syncLangPills(l);
    this.updateLangBadge();this.updateLangAlgoInfo();this.updateTechCompat();
  },

  syncLangPills(lang){
    document.querySelectorAll('.lang-opt').forEach(x=>{
      x.classList.toggle('active',x.dataset.lang===lang);
      const cb=this.querySelectorInput(x);if(cb)cb.checked=(x.dataset.lang===lang);
    });
  },

  updateLangBadge(){
    const badge=document.getElementById('lang-badge');if(!badge)return;
    const lang=this.state.language;
    badge.textContent=LANG_LABELS[lang]||'Auto';badge.title='Detected: '+lang;
  },

  updateLangAlgoInfo(){
    const info=document.getElementById('lang-algo-info');if(!info)return;
    const lang=this.state.language;
    info.innerHTML=LANG_ALGO_INFO[lang]||'Select a language to see best obfuscation algorithms for it.';
  },

  updateTechCompat(){
    const lang=this.state.language;
    const compat=LANG_TECH_COMPAT[lang]||LANG_TECH_COMPAT.generic;
    const strCompat=LANG_STRENCRYPT_COMPAT[lang]||LANG_STRENCRYPT_COMPAT.generic;
    document.querySelectorAll('.chk[data-cat]').forEach(el=>{
      const cat=el.dataset.cat;
      const tech=el.dataset.tech;
      const isSupported=compat.includes(cat);
      if(!isSupported){el.classList.add('unsupported');el.title='Not supported for '+lang}
      else if(cat==='strencrypt'){
        const isCipherOk=strCompat.includes(tech);
        if(!isCipherOk){el.classList.add('unsupported');el.title=tech.toUpperCase()+' not supported for '+lang}
        else{el.classList.remove('unsupported');el.title=''}
      } else {el.classList.remove('unsupported');el.title=''}
    });
  },

  calcStrength(){
    const s=this.state.pipeline;let score=0;
    const weights={transform:8,deadcode:5,controlflow:10,strencrypt:15,codevir:20,selfmod:18,instruct:6,opaque:12,antiai:10,watermark:3,polymorphic:7,split:8,stegano:5,codeenc:4,anti:6,wrapper:3,marshal:5,strarray:12,decompose:7,autoimport:4};
    s.forEach(step=>{score+=weights[step.cat]||0});
    if(s.length>3)score+=s.length*2;
    const layers=s.filter(x=>x.cat==='strencrypt').length;
    score+=layers*5;
    return Math.min(100,score);
  },

  getStrengthLabel(score){
    if(score>=90)return{label:'MAXIMUM',color:'#ff4466'};
    if(score>=70)return{label:'HIGH',color:'#ffaa44'};
    if(score>=50)return{label:'MEDIUM',color:'#44ddaa'};
    if(score>=30)return{label:'LOW',color:'#88aaff'};
    return{label:'MINIMAL',color:'#5a5a72'};
  },

  updateStats(){
    const e=this.el,s=this.state.stats;const cl=this.state.code.length;
    e.statLines.textContent=this.state.code?(this.state.code.match(/\n/g)||[]).length+1:0;
    e.statSize.textContent=cl?(cl/1024).toFixed(2)+' KB':'0 KB';
    e.statOutSize.textContent=s.final_size?(s.final_size/1024).toFixed(2)+' KB':'-';
    e.statRatio.textContent=s.expansion_ratio?(s.expansion_ratio*100).toFixed(0)+'%':'-';
    e.statEntropy.textContent=s.entropy?s.entropy.toFixed(4):'-';
    e.statEntropyBar.style.width=(s.entropy*100).toFixed(0)+'%';
    e.statEntropyBar.style.background=s.entropy>.7?'var(--acc)':s.entropy>.4?'var(--wrn)':'var(--tx2)';
    e.statSteps.textContent=s.steps.length||this.state.pipeline.length;
    const strength=this.calcStrength();const sl=this.getStrengthLabel(strength);
    e.statStrength.textContent=sl.label;e.statStrength.style.color=sl.color;
    e.statStrengthBar.style.width=strength+'%';e.statStrengthBar.style.background=sl.color;
  },

  exportPipeline(){
    const data={version:1,language:this.state.language,pipeline:this.state.pipeline};
    const blob=new Blob([JSON.stringify(data,null,2)],{type:'application/json'});
    const url=URL.createObjectURL(blob);
    const a=document.createElement('a');a.href=url;a.download='crypter0-pipeline.json';a.click();
    URL.revokeObjectURL(url);this.notify('Pipeline exported','success');
  },

  importPipeline(file){
    const r=new FileReader();r.onload=ev=>{
      try{
        const data=JSON.parse(ev.target.result);
        if(data.pipeline){
          this.pushHistory();
          this.state.pipeline=data.pipeline.map(s=>({
            cat:s.cat||s.category||'transform',
            tech:s.tech||s.technique||'simple',
            opts:s.opts||s.options||{}
          }));
          if(data.language){
            this.state.language=data.language;this.state._langManual=true;
            this.el.langSel.value=data.language;
            this.syncLangPills(data.language);
            this.updateLangBadge();this.updateLangAlgoInfo();this.updateTechCompat();
          }
          this.renderPipe();this.notify('Pipeline imported','success');
        }
      }catch(e){this.notify('Invalid pipeline file','error')}
    };r.readAsText(file);
  },

  loadLevel(level){
    const lang=this.state.language||'auto';
    fetch('api/presets.php?level='+level+'&lang='+encodeURIComponent(lang))
      .then(r=>r.json()).then(data=>{
        if(data.pipeline){
          this.pushHistory();
          this.state.pipeline=data.pipeline.map(s=>({
            cat:s.category||s.cat||'transform',
            tech:s.technique||s.tech||'simple',
            opts:s.options||s.opts||{}
          }));
          this.renderPipe();
          this.notify('Loaded '+data.name+' preset','success');
          this.autoAddFeatures(level);
        }
      }).catch(()=>{this.notify('Could not load preset','error')});
  },

  autoAddFeatures(level){
    if(!level)return;
    const lv=parseInt(level.value||level,10);
    if(isNaN(lv)||lv<6)return;
    const lang=this.state.language;
    if(!LANG_AUTO_FEATURES[lang])return;
    const af=LANG_AUTO_FEATURES[lang];
    if(af.anti){
      const existingIdx=this.state.pipeline.findIndex(s=>s.cat==='anti');
      if(existingIdx===-1){
        const opts={};af.anti.forEach(a=>{opts[a]=true});
        this.state.pipeline.push({cat:'anti',tech:'debug',opts});
        this.renderPipe();
      }else{
        const existing=this.state.pipeline[existingIdx];
        if(!existing.opts)existing.opts={};
        af.anti.forEach(a=>{existing.opts[a]=true});
        this.renderPipe();
      }
    }
  },

  setLanguage(lang,manual){
    this.state.language=lang;
    this.state._langManual=manual;
    this.el.langSel.value=lang;
    this.syncLangPills(lang);
    this.updateLangBadge();this.updateLangAlgoInfo();this.updateTechCompat();
  },

  initSearch(){
    const input=this.el.searchInput;if(!input)return;
    input.addEventListener('input',()=>{
      this._searchQuery=input.value;
      this.renderPipe();
      document.querySelectorAll('.side-sec').forEach(sec=>{
        const title=sec.querySelector('.side-sec-title');
        const chkg=sec.querySelector('.chkg');
        if(!chkg)return;
        const q=input.value.toLowerCase();
        if(!q){sec.style.display='';return}
        const labels=Array.from(chkg.querySelectorAll('.chk')).map(c=>c.textContent.toLowerCase());
        const matches=labels.some(l=>l.includes(q));
        sec.style.display=matches?'':'none';
      });
    });
  },

  initTheme(){
    const btn=this.el.themeBtn;if(!btn)return;
    const saved=localStorage.getItem('crypter0_theme');
    if(saved==='light')document.body.classList.add('light-theme');
    btn.textContent=document.body.classList.contains('light-theme')?'☀':'☾';
    btn.addEventListener('click',()=>{
      document.body.classList.toggle('light-theme');
      const isLight=document.body.classList.contains('light-theme');
      localStorage.setItem('crypter0_theme',isLight?'light':'dark');
      btn.textContent=isLight?'☀':'☾';
    });
  },

  initKeyboardShortcuts(){
    document.addEventListener('keydown',ev=>{
      if(ev.key==='Escape'){
        if(this.el.modalBg.classList.contains('active')){this.closeModal();return}
        if(this.el.shortcutsOverlay&&this.el.shortcutsOverlay.classList.contains('active')){
          this.el.shortcutsOverlay.classList.remove('active');return;
        }
      }
      if((ev.ctrlKey||ev.metaKey)&&ev.key==='Enter'){ev.preventDefault();this.process();return}
      if((ev.ctrlKey||ev.metaKey)&&ev.key==='z'&&!ev.shiftKey){ev.preventDefault();this.undo();return}
      if((ev.ctrlKey||ev.metaKey)&&(ev.key==='y'||(ev.key==='z'&&ev.shiftKey))){ev.preventDefault();this.redo();return}
      if(ev.key==='?'&&!ev.target.closest('input,textarea,select')){
        if(this.el.shortcutsOverlay)this.el.shortcutsOverlay.classList.toggle('active');
      }
    });
  },

  initAutoSave(){
    try{
      const saved=localStorage.getItem('crypter0_state');
      if(saved){
        const data=JSON.parse(saved);
        if(data.code){this.state.code=data.code;this.el.inputCode.value=data.code}
        if(data.language&&data.language!=='auto'){this.setLanguage(data.language,true)}
        if(data.pipeline&&data.pipeline.length){this.state.pipeline=data.pipeline;this.renderPipe()}
      }
    }catch(e){}
    this._autoSaveTimer=setInterval(()=>this.saveState(),30000);
  },

  saveState(){
    try{
      localStorage.setItem('crypter0_state',JSON.stringify({
        code:this.state.code,language:this.state.language,
        pipeline:this.state.pipeline
      }));
    }catch(e){}
  },

  initAnalyze(){
    const btn=this.el.analyzeBtn;if(!btn)return;
    btn.addEventListener('click',async()=>{
      const code=this.state.code;
      if(!code){this.notify('Enter code to analyze','error');return}
      try{
        const fd=new FormData();fd.append('code',code);
        const resp=await fetch('api/analyze.php',{method:'POST',body:fd});
        const data=await resp.json();
        if(!data.success){this.notify('Analyze failed','error');return}
        const panel=this.el.analyzePanel;if(!panel)return;
        let html=`<div class="analyze-title">Analysis Results</div>`;
        html+=`<div class="analyze-row"><span class="analyze-key">Language</span><span class="analyze-val">${escHtml(data.language)}</span></div>`;
        html+=`<div class="analyze-row"><span class="analyze-key">Lines</span><span class="analyze-val">${data.lines}</span></div>`;
        html+=`<div class="analyze-row"><span class="analyze-key">Size</span><span class="analyze-val">${(data.size/1024).toFixed(1)} KB</span></div>`;
        html+=`<div class="analyze-row"><span class="analyze-key">Entropy</span><span class="analyze-val">${data.entropy}</span></div>`;
        if(data.suggestions&&data.suggestions.length){
          html+=`<div class="analyze-row"><span class="analyze-key">Suggestions</span><span class="analyze-val">${data.suggestions.join(', ')}</span></div>`;
        }
        if(data.entropy>0.8){
          html+=`<div class="analyze-warn">High entropy detected — code may already be obfuscated</div>`;
        }
        panel.innerHTML=html;
        panel.style.display='block';
        this.notify('Analysis complete','success');
      }catch(err){this.notify('Analyze error: '+err.message,'error')}
    });
  },

  initMobileSidebar(){
    const toggle=document.querySelector('.side-toggle');
    const side=document.querySelector('.side');
    if(!toggle||!side)return;
    const overlay=document.querySelector('.side-overlay');
    const closeSidebar=()=>{side.classList.remove('open');toggle.textContent='☰';if(overlay)overlay.style.display='none'};
    const openSidebar=()=>{side.classList.add('open');toggle.textContent='✕';if(overlay)overlay.style.display='block'};
    toggle.addEventListener('click',()=>{
      if(side.classList.contains('open'))closeSidebar();else openSidebar();
    });
    if(overlay)overlay.addEventListener('click',closeSidebar);
    document.querySelectorAll('.chk[data-cat]').forEach(el=>{
      el.addEventListener('click',()=>{
        if(window.innerWidth<=768)setTimeout(closeSidebar,300);
      });
    });
    document.querySelectorAll('.lang-opt').forEach(el=>{
      el.addEventListener('click',()=>{
        if(window.innerWidth<=768)setTimeout(closeSidebar,300);
      });
    });
    let touchStartX=0;
    side.addEventListener('touchstart',e=>{touchStartX=e.touches[0].clientX},{passive:true});
    side.addEventListener('touchend',e=>{
      const diff=e.changedTouches[0].clientX-touchStartX;
      if(diff>80)closeSidebar();
    },{passive:true});
  },

  notify(msg,type){
    const e=this.el.notify;e.textContent=msg;e.className='notify '+(type||'');
    e.classList.add('show');clearTimeout(this._nt);this._nt=setTimeout(()=>e.classList.remove('show'),3500);
  }
};

document.addEventListener('DOMContentLoaded',()=>{A.init();window._App=A});
})();
