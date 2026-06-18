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

$python = '/usr/local/bin/python3';
$base   = dirname(__DIR__);

function run_script($python, $base, $script_name, $input, $output, $extra_args) {
    $script = $base . '/scripts/' . $script_name;
    $cmd = $python . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($input);
    if ($output !== '') $cmd .= ' ' . escapeshellarg($output);
    foreach ($extra_args as $k => $v) {
        $cmd .= ' ' . escapeshellarg("--$k") . ' ' . escapeshellarg($v);
    }
    $cmd .= ' 2>&1';
    return popen($cmd, 'r');
}

function stream_proc($proc, &$sse_fn) {
    while (!feof($proc)) {
        $line = fgets($proc, 4096);
        if ($line !== false && trim($line) !== '') {
            $sse_fn(['line' => rtrim($line)]);
        }
    }
    pclose($proc);
}

$resize_args = [];
if (isset($_GET['size']))  $resize_args['size'] = (string)(int)$_GET['size'];
if (isset($_GET['bg']))    $resize_args['bg']   = preg_replace('/[^0-9,]/', '', $_GET['bg']);

$compress_args = [];
if (isset($_GET['jpg_quality'])) $compress_args['jpg-quality'] = (string)(int)$_GET['jpg_quality'];
if (isset($_GET['png_level']))   $compress_args['png-level']   = (string)(int)$_GET['png_level'];

$out_dir = $out !== '' ? str_replace('~', '/Users/deffipy', $out) : '';

if ($action === 'resize') {
    $proc = run_script($python, $base, 'ec_resize.py', $dir, $out_dir, $resize_args);
    stream_proc($proc, 'sse');

} elseif ($action === 'compress') {
    $proc = run_script($python, $base, 'ec_compress.py', $dir, $out_dir, $compress_args);
    stream_proc($proc, 'sse');

} elseif ($action === 'both') {
    // 先 resize，输出到 resize_output，再把 resize_output 压缩
    $resize_out = ($out_dir !== '' ? $out_dir : $dir . '/output') . '/resized';
    sse(['line' => '── 第一步：调整尺寸 ──']);
    $proc = run_script($python, $base, 'ec_resize.py', $dir, $resize_out, $resize_args);
    stream_proc($proc, 'sse');

    $compress_out = ($out_dir !== '' ? $out_dir : $dir . '/output') . '/compressed';
    sse(['line' => '']);
    sse(['line' => '── 第二步：压缩图片 ──']);
    $proc = run_script($python, $base, 'ec_compress.py', $resize_out, $compress_out, $compress_args);
    stream_proc($proc, 'sse');
}

sse(['done' => true]);
