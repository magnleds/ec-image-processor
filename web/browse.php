<?php
header('Content-Type: application/json');

$root = '/Users/deffipy';
$path = realpath($_GET['path'] ?? $root);

if (!$path || strpos($path, $root) !== 0) {
    echo json_encode(['error' => 'path not allowed']);
    exit;
}

$img_exts = ['jpg','jpeg','png','webp','bmp','gif','tiff'];

function count_images($dir, $exts, $recursive = false) {
    $count = 0;
    foreach (scandir($dir) as $item) {
        if ($item[0] === '.') continue;
        $full = $dir . '/' . $item;
        if (is_file($full) && in_array(strtolower(pathinfo($item, PATHINFO_EXTENSION)), $exts)) {
            $count++;
        } elseif ($recursive && is_dir($full)) {
            $count += count_images($full, $exts, true);
        }
    }
    return $count;
}

$dirs = [];
foreach (scandir($path) as $item) {
    if ($item[0] === '.') continue;
    $full = $path . '/' . $item;
    if (is_dir($full)) $dirs[] = ['name' => $item, 'path' => $full];
}

echo json_encode([
    'path'            => $path,
    'parent'          => dirname($path) !== $path && strpos(dirname($path), $root) === 0 ? dirname($path) : null,
    'dirs'            => $dirs,
    'img_count'       => count_images($path, $img_exts, false),
    'img_count_deep'  => count_images($path, $img_exts, true),
]);
