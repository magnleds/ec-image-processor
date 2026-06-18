<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<title>图片批量处理</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, 'PingFang SC', sans-serif; background: #f0f2f5; min-height: 100vh; padding: 28px 20px; }
h1 { text-align: center; color: #1a1a2e; margin-bottom: 28px; font-size: 20px; font-weight: 700; letter-spacing: .5px; }

/* Layout */
.layout { max-width: 960px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; }
.top-row { display: flex; gap: 20px; flex-wrap: wrap; }
.card { background: #fff; border-radius: 12px; padding: 22px 24px; box-shadow: 0 2px 10px rgba(0,0,0,.07); }
.card-dir  { flex: 1; min-width: 280px; }
.card-mode { flex: 0 0 260px; }
.card-params { display: flex; gap: 20px; flex-wrap: wrap; }
.card-params .card { flex: 1; min-width: 260px; }
.card-console { }

/* Section title */
.sec-title { font-size: 13px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .8px; margin-bottom: 14px; }

/* Dir input */
.dir-row { display: flex; gap: 8px; align-items: center; margin-bottom: 10px; }
.dir-label { font-size: 12px; color: #666; width: 64px; flex-shrink: 0; }
.dir-input { flex: 1; display: flex; gap: 6px; }
.dir-input input {
  flex: 1; padding: 8px 10px; border: 1px solid #ddd; border-radius: 7px;
  font-size: 13px; color: #333; outline: none; transition: border .15s;
  font-family: 'SF Mono', Menlo, monospace;
}
.dir-input input:focus { border-color: #4f6ef7; }
.dir-input input.has-val { background: #f8f9ff; }
.btn-browse { padding: 8px 12px; background: #f0f2f5; border: 1px solid #ddd; border-radius: 7px; cursor: pointer; font-size: 13px; white-space: nowrap; transition: all .15s; }
.btn-browse:hover { background: #e6e9f0; }
.img-hint { font-size: 12px; color: #18a058; height: 16px; }
.scope-row { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
.scope-label { font-size: 12px; color: #666; }
.scope-btns { display: flex; gap: 0; border: 1px solid #ddd; border-radius: 7px; overflow: hidden; }
.scope-btn { padding: 5px 14px; font-size: 12px; background: #fff; border: none; cursor: pointer; color: #666; transition: all .15s; }
.scope-btn.active { background: #4f6ef7; color: #fff; font-weight: 600; }
.scope-btn:first-child { border-right: 1px solid #ddd; }

/* Mode selector */
.mode-btns { display: flex; flex-direction: column; gap: 8px; }
.mode-btn {
  padding: 10px 14px; border: 2px solid #eee; border-radius: 9px;
  cursor: pointer; font-size: 13px; font-weight: 600; background: #fff;
  transition: all .15s; text-align: left; display: flex; align-items: center; gap: 10px;
}
.mode-btn:hover { border-color: #bbb; }
.mode-btn.active-resize   { border-color: #4f6ef7; background: #f0f3ff; color: #3b58e0; }
.mode-btn.active-compress { border-color: #18a058; background: #f0faf4; color: #168a49; }
.mode-btn.active-both     { border-color: #e07b18; background: #fff8f0; color: #c96c10; }
.mode-icon { font-size: 18px; }
.mode-sub  { font-size: 11px; font-weight: 400; color: #999; margin-top: 1px; }

/* Params */
.param-row { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.param-row:last-child { margin-bottom: 0; }
.param-label { font-size: 12px; color: #555; width: 80px; flex-shrink: 0; }
.param-row input[type=number], .param-row input[type=range] { flex: 1; }
input[type=number] {
  padding: 6px 9px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; outline: none; width: 70px;
}
input[type=number]:focus { border-color: #4f6ef7; }
input[type=range] { accent-color: #4f6ef7; cursor: pointer; }
.param-val { font-size: 13px; font-weight: 600; color: #333; width: 32px; text-align: right; }
.color-swatch { display: flex; gap: 6px; flex-wrap: wrap; }
.swatch { width: 26px; height: 26px; border-radius: 6px; cursor: pointer; border: 2px solid transparent; transition: all .15s; }
.swatch:hover { transform: scale(1.1); }
.swatch.sel { border-color: #4f6ef7; box-shadow: 0 0 0 2px rgba(79,110,247,.3); }

/* Run button */
.run-wrap { display: flex; justify-content: center; }
button.run {
  padding: 12px 48px; border: none; border-radius: 10px;
  font-size: 15px; font-weight: 700; cursor: pointer; transition: all .2s; color: #fff;
  display: flex; align-items: center; gap: 8px;
}
.run-resize   { background: #4f6ef7; }
.run-resize:hover   { background: #3b5de6; }
.run-compress { background: #18a058; }
.run-compress:hover { background: #138a45; }
.run-both     { background: #e07b18; }
.run-both:hover { background: #c96c10; }
button.run:disabled { opacity: .5; cursor: not-allowed; }

/* Console */
.console-wrap { display: none; }
.console-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.console-header span { font-size: 13px; font-weight: 600; color: #555; }
.btn-clear { font-size: 12px; color: #999; cursor: pointer; background: none; border: none; padding: 2px 6px; border-radius: 4px; }
.btn-clear:hover { background: #f0f0f0; color: #555; }
.console {
  background: #1e1e2e; border-radius: 9px; padding: 14px 16px;
  min-height: 100px; max-height: 360px; overflow-y: auto;
}
.console p { font-family: 'SF Mono', Menlo, monospace; font-size: 12px; line-height: 1.8; color: #cdd6f4; white-space: pre-wrap; }
.console p.done  { color: #a6e3a1; font-weight: 700; }
.console p.error { color: #f38ba8; }
.console p.sep   { color: #6c7086; }

/* Browse modal */
.modal-overlay {
  display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45);
  z-index: 100; align-items: center; justify-content: center;
}
.modal-overlay.show { display: flex; }
.modal {
  background: #fff; border-radius: 14px; width: 520px; max-width: 95vw;
  box-shadow: 0 20px 60px rgba(0,0,0,.25); overflow: hidden;
  animation: pop .18s ease;
}
@keyframes pop { from { transform: scale(.92); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.modal-head {
  padding: 16px 20px; border-bottom: 1px solid #f0f0f0;
  display: flex; align-items: center; justify-content: space-between;
}
.modal-head h3 { font-size: 15px; color: #1a1a2e; }
.modal-path { font-size: 11px; color: #888; font-family: 'SF Mono', Menlo, monospace; margin-top: 3px; max-width: 380px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.btn-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #999; line-height: 1; padding: 0 4px; }
.btn-close:hover { color: #333; }
.modal-body { max-height: 340px; overflow-y: auto; }
.dir-item {
  padding: 11px 20px; display: flex; align-items: center; gap: 10px;
  cursor: pointer; transition: background .1s; border-bottom: 1px solid #fafafa;
}
.dir-item:hover { background: #f5f7ff; }
.dir-item .icon { font-size: 18px; }
.dir-item .name { font-size: 14px; color: #333; flex: 1; }
.dir-item .arrow { color: #bbb; font-size: 14px; }
.dir-item.up { color: #888; }
.dir-item.up .name { color: #888; font-size: 13px; }
.dir-item.current { background: #f0f3ff; }
.dir-item.current .name { color: #4f6ef7; font-weight: 600; }
.modal-foot {
  padding: 14px 20px; border-top: 1px solid #f0f0f0;
  display: flex; justify-content: space-between; align-items: center; gap: 12px;
}
.modal-img-count { font-size: 13px; color: #18a058; font-weight: 500; }
.modal-foot-btns { display: flex; gap: 8px; }
.btn-cancel { padding: 8px 18px; background: #f0f2f5; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; }
.btn-select { padding: 8px 18px; background: #4f6ef7; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; }
.btn-select:hover { background: #3b5de6; }
.modal-loading { padding: 24px; text-align: center; color: #999; font-size: 13px; }

.spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; vertical-align: middle; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
</head>
<body>
<h1>⚡ 图片批量处理</h1>

<div class="layout">

  <div class="top-row">
    <!-- 目录 -->
    <div class="card card-dir">
      <div class="sec-title">目录设置</div>
      <div class="dir-row">
        <span class="dir-label">输入目录</span>
        <div class="dir-input">
          <input type="text" id="in-dir" placeholder="/Users/deffipy/...">
          <button class="btn-browse" onclick="openBrowse('in')">📁 选择</button>
        </div>
      </div>
      <div class="dir-row">
        <span class="dir-label">输出目录</span>
        <div class="dir-input">
          <input type="text" id="out-dir" placeholder="留空则自动创建 output 子目录">
          <button class="btn-browse" onclick="openBrowse('out')">📁 选择</button>
        </div>
      </div>
      <div class="scope-row">
        <div class="scope-btns">
          <button class="scope-btn active" id="scope-local" onclick="setScope(0)">本目录</button>
          <button class="scope-btn" id="scope-deep" onclick="setScope(1)">含子目录</button>
        </div>
        <div class="img-hint" id="img-hint"></div>
      </div>
    </div>

    <!-- 模式 -->
    <div class="card card-mode">
      <div class="sec-title">运行模式</div>
      <div class="mode-btns">
        <button class="mode-btn active-resize" data-mode="resize" onclick="setMode('resize')">
          <span class="mode-icon">📐</span>
          <div><div>仅调整尺寸</div><div class="mode-sub">等比缩放 + 白底</div></div>
        </button>
        <button class="mode-btn" data-mode="compress" onclick="setMode('compress')">
          <span class="mode-icon">🗜️</span>
          <div><div>仅压缩图片</div><div class="mode-sub">PNG lossless · JPG optimize</div></div>
        </button>
        <button class="mode-btn" data-mode="both" onclick="setMode('both')">
          <span class="mode-icon">⚡</span>
          <div><div>调整 + 压缩</div><div class="mode-sub">先 resize 再 compress</div></div>
        </button>
      </div>
    </div>
  </div>

  <!-- 参数 -->
  <div class="card-params">
    <div class="card" id="params-resize">
      <div class="sec-title">调整尺寸参数</div>
      <div class="param-row">
        <span class="param-label">输出尺寸</span>
        <input type="range" id="p-size" min="400" max="2000" step="100" value="800" oninput="document.getElementById('v-size').textContent=this.value">
        <span class="param-val" id="v-size">800</span>
        <span style="font-size:12px;color:#999">px</span>
      </div>
      <div class="param-row">
        <span class="param-label">背景色</span>
        <div class="color-swatch">
          <div class="swatch sel" style="background:#fff;border:1px solid #ddd" data-bg="255,255,255" onclick="setBg(this)" title="白色"></div>
          <div class="swatch" style="background:#f5f5f5;border:1px solid #ddd" data-bg="245,245,245" onclick="setBg(this)" title="浅灰"></div>
          <div class="swatch" style="background:#000" data-bg="0,0,0" onclick="setBg(this)" title="黑色"></div>
          <div class="swatch" style="background:#ffe4e1" data-bg="255,228,225" onclick="setBg(this)" title="浅粉"></div>
        </div>
        <span style="font-size:11px;color:#aaa;margin-left:4px" id="v-bg">255,255,255</span>
      </div>
    </div>

    <div class="card" id="params-compress">
      <div class="sec-title">压缩参数</div>
      <div class="param-row">
        <span class="param-label">JPG 质量</span>
        <input type="range" id="p-jpgq" min="50" max="100" step="1" value="85" oninput="document.getElementById('v-jpgq').textContent=this.value">
        <span class="param-val" id="v-jpgq">85</span>
      </div>
      <div class="param-row">
        <span class="param-label">PNG 等级</span>
        <input type="range" id="p-pngl" min="1" max="6" step="1" value="4" oninput="document.getElementById('v-pngl').textContent=this.value">
        <span class="param-val" id="v-pngl">4</span>
        <span style="font-size:12px;color:#999">/6</span>
      </div>
    </div>
  </div>

  <!-- 运行 -->
  <div class="run-wrap">
    <button class="run run-resize" id="run-btn" onclick="runTask()">▶ 开始处理</button>
  </div>

  <!-- 控制台 -->
  <div class="card card-console console-wrap" id="console-wrap">
    <div class="console-header">
      <span>输出日志</span>
      <button class="btn-clear" onclick="clearConsole()">清空</button>
    </div>
    <div class="console" id="console"></div>
  </div>

</div>

<!-- 目录浏览弹窗 -->
<div class="modal-overlay" id="modal">
  <div class="modal">
    <div class="modal-head">
      <div>
        <h3>选择目录</h3>
        <div class="modal-path" id="modal-path">/Users/deffipy</div>
      </div>
      <button class="btn-close" onclick="closeModal()">×</button>
    </div>
    <div class="modal-body" id="modal-body">
      <div class="modal-loading">加载中…</div>
    </div>
    <div class="modal-foot">
      <span class="modal-img-count" id="modal-img-count"></span>
      <div class="modal-foot-btns">
        <button class="btn-cancel" onclick="closeModal()">取消</button>
        <button class="btn-select" onclick="selectDir()">选择此目录</button>
      </div>
    </div>
  </div>
</div>

<script>
const LS_IN  = 'ec_in_dir';
const LS_SCOPE = 'ec_scope';
let currentScope = 0; // 0=本目录 1=含子目录
const LS_OUT = 'ec_out_dir';
let currentMode = 'resize';
let currentBg   = '255,255,255';
let browseTarget = 'in';   // 'in' or 'out'
let browsePath   = '/Users/deffipy';
let currentImgCount = 0;
let activeES = null;

// ── 初始化：从 localStorage 恢复路径 ──
window.onload = () => {
  const inDir  = localStorage.getItem(LS_IN)  || '';
  const outDir = localStorage.getItem(LS_OUT) || '';
  currentScope = parseInt(localStorage.getItem(LS_SCOPE) || '0');
  setScope(currentScope, true);
  if (inDir)  { document.getElementById('in-dir').value = inDir; checkImgCount(inDir); }
  if (outDir) document.getElementById('out-dir').value = outDir;
};

// ── 模式切换 ──
function setMode(mode) {
  currentMode = mode;
  document.querySelectorAll('.mode-btn').forEach(b => {
    b.className = 'mode-btn';
    if (b.dataset.mode === mode) b.classList.add('active-' + mode);
  });
  const btn = document.getElementById('run-btn');
  const labels = { resize: '▶ 开始调整尺寸', compress: '▶ 开始压缩', both: '▶ 调整 + 压缩' };
  const cls    = { resize: 'run-resize', compress: 'run-compress', both: 'run-both' };
  btn.textContent = labels[mode];
  btn.className = 'run ' + cls[mode];
}

// ── 背景色切换 ──
function setBg(el) {
  document.querySelectorAll('.swatch').forEach(s => s.classList.remove('sel'));
  el.classList.add('sel');
  currentBg = el.dataset.bg;
  document.getElementById('v-bg').textContent = currentBg;
}

// ── 扫描范围切换 ──
function setScope(v, silent) {
  currentScope = v;
  if (!silent) localStorage.setItem(LS_SCOPE, v);
  document.getElementById('scope-local').classList.toggle('active', v === 0);
  document.getElementById('scope-deep').classList.toggle('active', v === 1);
  const dir = document.getElementById('in-dir').value.trim();
  if (dir) checkImgCount(dir);
}

// ── 检查目录图片数 ──
function checkImgCount(path) {
  if (!path) { document.getElementById('img-hint').textContent = ''; return; }
  fetch('browse.php?path=' + encodeURIComponent(path))
    .then(r => r.json())
    .then(d => {
      const n  = currentScope === 1 ? (d.img_count_deep || 0) : (d.img_count || 0);
      const nd = d.img_count_deep || 0;
      if (n > 0) {
        document.getElementById('img-hint').textContent = '✓ 找到 ' + n + ' 张图片';
      } else if (currentScope === 0 && nd > 0) {
        document.getElementById('img-hint').innerHTML = '<span style="color:#e07b18">本目录无图片，子目录共 ' + nd + ' 张</span>';
      } else {
        document.getElementById('img-hint').textContent = '该目录没有图片';
      }
    }).catch(() => {});
}

document.getElementById('in-dir').addEventListener('change', e => {
  localStorage.setItem(LS_IN, e.target.value);
  checkImgCount(e.target.value);
});
document.getElementById('out-dir').addEventListener('change', e => {
  localStorage.setItem(LS_OUT, e.target.value);
});

// ── 目录浏览 ──
function openBrowse(target) {
  browseTarget = target;
  const cur = document.getElementById(target === 'in' ? 'in-dir' : 'out-dir').value;
  browsePath = cur || '/Users/deffipy';
  document.getElementById('modal').classList.add('show');
  loadDir(browsePath);
}
function closeModal() {
  document.getElementById('modal').classList.remove('show');
}
function loadDir(path) {
  document.getElementById('modal-path').textContent = path;
  document.getElementById('modal-body').innerHTML = '<div class="modal-loading">加载中…</div>';
  document.getElementById('modal-img-count').textContent = '';
  fetch('browse.php?path=' + encodeURIComponent(path))
    .then(r => r.json())
    .then(d => {
      if (d.error) { document.getElementById('modal-body').innerHTML = `<div class="modal-loading">${d.error}</div>`; return; }
      browsePath = d.path;
      document.getElementById('modal-path').textContent = d.path;
      const n = d.img_count;
      document.getElementById('modal-img-count').textContent = n > 0 ? `📷 ${n} 张图片` : '';
      let html = '';
      if (d.parent) {
        html += `<div class="dir-item up" onclick="loadDir('${esc(d.parent)}')"><span class="icon">↩</span><span class="name">上一级</span></div>`;
      }
      if (d.dirs.length === 0 && !d.parent) {
        html += '<div class="modal-loading" style="color:#ccc">没有子目录</div>';
      }
      d.dirs.forEach(dir => {
        html += `<div class="dir-item" onclick="loadDir('${esc(dir.path)}')"><span class="icon">📁</span><span class="name">${esc2(dir.name)}</span><span class="arrow">›</span></div>`;
      });
      document.getElementById('modal-body').innerHTML = html;
    })
    .catch(() => { document.getElementById('modal-body').innerHTML = '<div class="modal-loading">加载失败</div>'; });
}
function selectDir() {
  const input = document.getElementById(browseTarget === 'in' ? 'in-dir' : 'out-dir');
  input.value = browsePath;
  localStorage.setItem(browseTarget === 'in' ? LS_IN : LS_OUT, browsePath);
  if (browseTarget === 'in') checkImgCount(browsePath);
  closeModal();
}
function esc(s)  { return s.replace(/'/g, "\\'"); }
function esc2(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

// 点击遮罩关闭
document.getElementById('modal').addEventListener('click', e => {
  if (e.target === document.getElementById('modal')) closeModal();
});

// ── 运行 ──
function runTask() {
  const dir = document.getElementById('in-dir').value.trim();
  const out = document.getElementById('out-dir').value.trim();
  if (!dir) { alert('请先选择输入目录'); return; }

  if (activeES) { activeES.close(); activeES = null; }

  const btn = document.getElementById('run-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> 处理中…';

  const wrap = document.getElementById('console-wrap');
  const con  = document.getElementById('console');
  wrap.style.display = 'block';
  con.innerHTML = '';

  const params = new URLSearchParams({
    action:      currentMode,
    dir,
    size:        document.getElementById('p-size').value,
    bg:          currentBg,
    jpg_quality: document.getElementById('p-jpgq').value,
    png_level:   document.getElementById('p-pngl').value,
    recursive:   currentScope === 1 ? '1' : '0',
  });
  if (out) params.set('output', out);

  const es = new EventSource('process.php?' + params.toString());
  activeES = es;

  es.onmessage = e => {
    const data = JSON.parse(e.data);
    const p = document.createElement('p');
    if (data.error) {
      p.className = 'error'; p.textContent = '✖ ' + data.error;
      es.close(); activeES = null; resetBtn();
    } else if (data.done) {
      p.className = 'done'; p.textContent = '✔ 全部完成';
      es.close(); activeES = null; resetBtn();
    } else {
      const t = data.line;
      if (t.startsWith('──')) p.className = 'sep';
      p.textContent = t;
    }
    con.appendChild(p);
    con.scrollTop = con.scrollHeight;
  };
  es.onerror = () => {
    if (es.readyState === 2) {
      const p = document.createElement('p');
      p.className = 'error'; p.textContent = '连接中断';
      con.appendChild(p);
      activeES = null; resetBtn();
    }
  };
}

function resetBtn() {
  const btn = document.getElementById('run-btn');
  btn.disabled = false;
  const labels = { resize: '▶ 开始调整尺寸', compress: '▶ 开始压缩', both: '▶ 调整 + 压缩' };
  btn.textContent = labels[currentMode];
}

function clearConsole() {
  document.getElementById('console').innerHTML = '';
  document.getElementById('console-wrap').style.display = 'none';
}
</script>
</body>
</html>
