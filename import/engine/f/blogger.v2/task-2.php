<?php

$fetch = 'https://www.blogger.com/feeds/' . $query['blog'] . '/posts/summary?alt=json&max-results=0&start-index=1';

if ($r = require __DIR__ . DS . 'f.php') {
    return $r;
}

if (!empty($query['o']['tag'])) {
    $create = 0;
    if (!empty($data['feed']['category'])) {
        foreach ($data['feed']['category'] as $k => $v) {
            $n = To::kebab($title = $v['term'] ?? $k + 1);
            $file = is_file($f = $folder . DS . 'lot' . DS . 'tag' . DS . $n . '.page');
            if (!$safe || !$file) {
                if (!is_dir($d = Path::F($f))) {
                    mkdir($d, 0775, true);
                }
                file_put_contents($d . DS . 'id.data', $k + 1);
                file_put_contents($d . DS . 'time.data', date('Y-m-d H:i:s'));
                file_put_contents($f, To::page(is([
                    'title' => $title
                ]), function($v) {
                    return isset($v);
                }));
                $log[microtime()] = [
                    'status' => 201,
                    'description' => i('Tag %s successfully imported to %s', ['<strong>' . $title . '</strong>', '<code>' . strtr($f, [ROOT => '.']) . '</code>'])
                ];
                ++$create;
            } else if ($file) {
                $log[microtime()] = [
                    'status' => 304,
                    'description' => i('Tag %s already exists.', ['<strong>' . $title . '</strong>'])
                ];
            }
        }
    }
    if ($create > 0) {
        $log[microtime()] = [
            'status' => 201,
            'description' => i('%d tag' . (1 === $create ? "" : 's') . ' successfully imported to %s', [$create, '<code>' . strtr($folder . DS . 'lot' . DS . 'tag', [ROOT => '.']) . '</code>'])
        ];
    } else {
        $log[microtime()] = [
            'status' => 100,
            'description' => i('Continue') . '…'
        ];
    }
} else {
    $log[microtime()] = [
        'status' => 100,
        'description' => i('Tag importer was disabled by the author.')
    ];
}

$count = (int) ($data['feed']['openSearch$totalResults']['$t'] ?? 0);

$log[microtime()] = [
    'status' => 100,
    'description' => i('Found %d blog posts in total.', [$count])
];

$log[microtime()] = [
    'status' => 102,
    'description' => i('Importing blog ' . (0 === $count ? 'page' : 'post') . 's') . '…'
];

return [
    'log' => $log,
    'next' => $next ?? $url . '/.import/blogger.v2/task-' . (0 === $count ? '4' : '3' . $url->query('&', [
        'chunk' => $query['chunk'],
        'i' => 1
    ]))
];
