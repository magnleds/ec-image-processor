<?php
header('Content-Type: application/json');

$root = '/Users/deffipy';
$path = realpath($_GET['path'] ?? $root);

// 安全：只允许在 /Users/deffipy 下浏览
if (!$path || strpos($path, $root) !== 0) {
    echo json_encode(['error' => 'path not allowed']);
    exit;
}

$dirs = [];
foreach (scandir($path) as $item) {
    if ($item[0] === '.') continue;
    $full = $path . '/' . $item;
    if (is_dir($full)) {
        $dirs[] = ['name' => $item, 'path' => $full];
    }
}

// 统计图片数量
$img_exts = ['jpg','jpeg','png','webp','bmp','gif','tiff'];
$img_count = 0;
foreach (scandir($path) as $item) {
    if ($item[0] === '.') continue;
    $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
    if (in_array($ext, $img_exts) && is_file($path . '/' . $item)) $img_count++;
}

echo json_encode([
    'path'      => $path,
    'parent'    => dirname($path) !== $path && strpos(dirname($path), $root) === 0 ? dirname($path) : null,
    'dirs'      => $dirs,
    'img_count' => $img_count,
]);
