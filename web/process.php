<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

function sse($data) {
    echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}

$action = $_GET['action'] ?? '';
$dir    = trim($_GET['dir'] ?? '');
$out    = trim($_GET['output'] ?? '');

if (!in_array($action, ['resize', 'compress', 'both'], true)) {
    sse(['error' => '无效操作']); exit;
}
if ($dir === '') {
    sse(['error' => '请填写目录路径']); exit;
}

$dir = str_replace('~', '/Users/deffipy', $dir);
if (!is_dir($dir)) {
    sse(['error' => "目录不存在: $dir"]); exit;
}

$python    = '/usr/local/bin/python3.13';
$base      = dirname(__DIR__);
$recursive = !empty($_GET['recursive']) && $_GET['recursive'] === '1';

function build_cmd($python, $base, $script, $input, $output, $opts, $recursive) {
    $cmd = $python . ' ' . escapeshellarg($base . '/scripts/' . $script)
         . ' ' . escapeshellarg($input);
    if ($output !== '') $cmd .= ' ' . escapeshellarg($output);
    foreach ($opts as $k => $v) {
        $cmd .= ' ' . escapeshellarg("--$k") . ' ' . escapeshellarg($v);
    }
    if ($recursive) $cmd .= ' --recursive';
    return $cmd . ' 2>&1';
}

function stream($cmd) {
    $proc = popen($cmd, 'r');
    if (!$proc) { sse(['error' => '无法启动脚本']); return; }
    while (!feof($proc)) {
        $line = fgets($proc, 4096);
        if ($line !== false && trim($line) !== '') sse(['line' => rtrim($line)]);
    }
    pclose($proc);
}

$out_dir = $out !== '' ? str_replace('~', '/Users/deffipy', $out) : '';

$ropts = [];
if (isset($_GET['size'])) $ropts['size'] = (string)(int)$_GET['size'];
if (isset($_GET['bg']))   $ropts['bg']   = preg_replace('/[^0-9,]/', '', $_GET['bg']);

$copts = [];
if (isset($_GET['jpg_quality'])) $copts['jpg-quality'] = (string)(int)$_GET['jpg_quality'];
if (isset($_GET['png_level']))   $copts['png-level']   = (string)(int)$_GET['png_level'];

if ($action === 'resize') {
    stream(build_cmd($python, $base, 'ec_resize.py', $dir, $out_dir, $ropts, $recursive));

} elseif ($action === 'compress') {
    stream(build_cmd($python, $base, 'ec_compress.py', $dir, $out_dir, $copts, $recursive));

} elseif ($action === 'both') {
    $base_out    = $out_dir !== '' ? $out_dir : $dir . '/output';
    $resize_out  = $base_out . '/resized';
    $compress_out = $base_out . '/compressed';

    sse(['line' => '── 第一步：调整尺寸 ──']);
    stream(build_cmd($python, $base, 'ec_resize.py', $dir, $resize_out, $ropts, $recursive));

    sse(['line' => '']);
    sse(['line' => '── 第二步：压缩图片 ──']);
    // both 模式第二步不再需要 recursive（resized 是平铺结构）
    stream(build_cmd($python, $base, 'ec_compress.py', $resize_out, $compress_out, $copts, false));
}

sse(['done' => true]);
