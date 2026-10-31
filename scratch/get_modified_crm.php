<?php
$dir = 'c:/wamp64/www/hc_interior_live/crm.hcinterior.in';
$cutoff = strtotime('2026-10-23 00:00:00'); // Let's check last 48 hours to be thorough

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$modified = [];

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $mtime = $file->getMTime();
        if ($mtime >= $cutoff) {
            $path = str_replace('\\', '/', $file->getPathname());
            if (strpos($path, '/uploads/') === false && strpos($path, '/cache/') === false && strpos($path, '/logs/') === false && strpos($path, '/fonts/') === false) {
                $modified[] = [
                    'time' => date('Y-m-d H:i:s', $mtime),
                    'path' => str_replace('c:/wamp64/www/hc_interior_live/', '', $path)
                ];
            }
        }
    }
}

usort($modified, function($a, $b) {
    return strcmp($b['time'], $a['time']);
});

echo json_encode($modified, JSON_PRETTY_PRINT);
