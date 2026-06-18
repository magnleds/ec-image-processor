<?php
// SSE endpoint — streams python script output line by line

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

function sse($data) {
    echo 'data: ' . json_encode($data) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}

$action = $_GET['action'] ?? '';
$dir    = trim($_GET['dir'] ?? '');
$out    = trim($_GET['output'] ?? '');

if (!in_array($action, ['resize', 'compress'], true)) {
    sse(['error' => '无效操作']); exit;
}
if ($dir === '') {
    sse(['error' => '请填写目录路径']); exit;
}

$dir = str_replace('~', '/Users/deffipy', $dir);
if (!is_dir($dir)) {
    sse(['error' => "目录不存在: $dir"]); exit;
}

$base    = dirname(__DIR__);
$scripts = ['resize' => 'ec_resize.py', 'compress' => 'ec_compress.py'];
$script  = $base . '/scripts/' . $scripts[$action];

$python = '/usr/local/bin/python3';
foreach (['/usr/local/bin/python3', '/usr/bin/python3', '/opt/homebrew/bin/python3'] as $p) {
    if (file_exists($p)) { $python = $p; break; }
}

$cmd = $python . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($dir);
if ($out !== '') {
    $out = str_replace('~', '/Users/deffipy', $out);
    $cmd .= ' ' . escapeshellarg($out);
}
$cmd .= ' 2>&1';

$proc = popen($cmd, 'r');
if (!$proc) {
    sse(['error' => '无法启动脚本']); exit;
}

while (!feof($proc)) {
    $line = fgets($proc, 4096);
    if ($line !== false && trim($line) !== '') {
        sse(['line' => rtrim($line)]);
    }
}
pclose($proc);
sse(['done' => true]);
