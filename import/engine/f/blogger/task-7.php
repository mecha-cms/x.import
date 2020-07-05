<?php

$fetch = 'https://www.blogger.com/feeds/' . $query['blog'] . '/posts/summary?alt=json&max-results=0&start-index=1';

if ($r = require __DIR__ . DS . 'f.php') {
    return $r;
}

// Cleaning up silently…
$tsv = file_get_contents($f = $folder . DS . 'lot' . DS . 'page' . DS . 'kick.tsv');
if (false !== $tsv) {
    $unique = [];
    foreach (explode("\n", $tsv) as $v) {
        $v = explode("\t", $v);
        $unique[$v[0]] = $v[1];
    }
    $tsv = "";
    ksort($unique);
    foreach ($unique as $k => $v) {
        $tsv .= $k . "\t" . $v . "\n";
    }
    file_put_contents($f, rtrim($tsv, "\n"));
    @chmod($f, 0600);
}

// ditto
foreach (g($folder, 'archive,draft,page,php', true) as $k => $v) {
    if (1 === $v) {
        @chmod($k, 0600);
    }
}

return [
    'log' => [],
    'next' => true
];
