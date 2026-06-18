<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<title>图片批量处理</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, 'PingFang SC', sans-serif; background: #f0f2f5; min-height: 100vh; padding: 32px 20px; }
  h1 { text-align: center; color: #1a1a2e; margin-bottom: 32px; font-size: 22px; font-weight: 600; }
  .cards { display: flex; gap: 24px; max-width: 900px; margin: 0 auto; flex-wrap: wrap; }
  .card { flex: 1; min-width: 320px; background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
  .card h2 { font-size: 17px; color: #1a1a2e; margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
  .card p  { color: #666; font-size: 13px; margin-bottom: 20px; line-height: 1.5; }
  label { display: block; font-size: 13px; color: #444; margin-bottom: 5px; font-weight: 500; }
  input[type=text] {
    width: 100%; padding: 9px 12px; border: 1px solid #d9d9d9; border-radius: 8px;
    font-size: 13px; color: #333; outline: none; transition: border .2s;
  }
  input[type=text]:focus { border-color: #4f6ef7; }
  .row { margin-bottom: 14px; }
  button.run {
    width: 100%; margin-top: 8px; padding: 10px; border: none; border-radius: 8px;
    font-size: 14px; font-weight: 600; cursor: pointer; transition: all .2s; color: #fff;
  }
  .btn-resize   { background: #4f6ef7; }
  .btn-resize:hover   { background: #3b5de6; }
  .btn-compress { background: #18a058; }
  .btn-compress:hover { background: #1a8a4a; }
  button.run:disabled { opacity: .55; cursor: not-allowed; }
  .console {
    margin-top: 20px; background: #1e1e2e; border-radius: 8px; padding: 14px 16px;
    min-height: 120px; max-height: 320px; overflow-y: auto; display: none;
  }
  .console p { font-family: 'SF Mono', Menlo, monospace; font-size: 12px; line-height: 1.7; color: #cdd6f4; white-space: pre-wrap; }
  .console p.done  { color: #a6e3a1; font-weight: 600; }
  .console p.error { color: #f38ba8; }
  .badge { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
  .badge-blue  { background: #e8edff; color: #4f6ef7; }
  .badge-green { background: #e6f7ee; color: #18a058; }
  .spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; vertical-align: middle; margin-right: 6px; }
  @keyframes spin { to { transform: rotate(360deg); } }
</style>
</head>
<body>
<h1>⚡ 图片批量处理</h1>
<div class="cards">

  <!-- 调整尺寸 -->
  <div class="card">
    <h2><span class="badge badge-blue">Resize</span> 调整尺寸</h2>
    <p>等比缩放至 800×800，透明背景自动补白底，居中放置。支持 JPG / PNG / WebP 等格式。</p>
    <div class="row">
      <label>输入目录</label>
      <input type="text" id="r-dir" placeholder="~/Desktop/产品图">
    </div>
    <div class="row">
      <label>输出目录 <span style="color:#999;font-weight:400">（留空则自动创建 output 子目录）</span></label>
      <input type="text" id="r-out" placeholder="留空使用默认">
    </div>
    <button class="run btn-resize" onclick="run('resize')">▶ 开始调整尺寸</button>
    <div class="console" id="con-resize"></div>
  </div>

  <!-- 压缩图片 -->
  <div class="card">
    <h2><span class="badge badge-green">Compress</span> 压缩图片</h2>
    <p>PNG 无损压缩（oxipng level 4），JPG 高质量压缩（quality 85 + optimize）。</p>
    <div class="row">
      <label>输入目录</label>
      <input type="text" id="c-dir" placeholder="~/Desktop/产品图">
    </div>
    <div class="row">
      <label>输出目录 <span style="color:#999;font-weight:400">（留空则自动创建 output 子目录）</span></label>
      <input type="text" id="c-out" placeholder="留空使用默认">
    </div>
    <button class="run btn-compress" onclick="run('compress')">▶ 开始压缩</button>
    <div class="console" id="con-compress"></div>
  </div>

</div>

<script>
function run(action) {
  const isResize = action === 'resize';
  const dir = document.getElementById(isResize ? 'r-dir' : 'c-dir').value.trim();
  const out = document.getElementById(isResize ? 'r-out' : 'c-out').value.trim();
  const btn = document.querySelector(isResize ? '.btn-resize' : '.btn-compress');
  const con = document.getElementById('con-' + action);

  if (!dir) { alert('请填写输入目录'); return; }

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span>处理中…';
  con.style.display = 'block';
  con.innerHTML = '';

  const params = new URLSearchParams({ action, dir });
  if (out) params.set('output', out);
  const es = new EventSource('process.php?' + params.toString());

  es.onmessage = e => {
    const data = JSON.parse(e.data);
    const p = document.createElement('p');
    if (data.error) {
      p.className = 'error';
      p.textContent = '✖ ' + data.error;
      es.close();
      finish(btn, action);
    } else if (data.done) {
      p.className = 'done';
      p.textContent = '✔ 全部完成';
      es.close();
      finish(btn, action);
    } else {
      p.textContent = data.line;
    }
    con.appendChild(p);
    con.scrollTop = con.scrollHeight;
  };

  es.onerror = () => {
    const p = document.createElement('p');
    p.className = 'error';
    p.textContent = '连接中断';
    con.appendChild(p);
    es.close();
    finish(btn, action);
  };
}

function finish(btn, action) {
  btn.disabled = false;
  btn.textContent = action === 'resize' ? '▶ 开始调整尺寸' : '▶ 开始压缩';
}
</script>
</body>
</html>
