<?php

$content = fetch('https://www.blogger.com/feeds/' . $query['blog'] . '/posts/summary?alt=json&max-results=0&start-index=1');

require __DIR__ . DS . 'f.php';

foreach ([
    'comment',
    'page',
    'page' . DS . 'blog',
    'tag',
    'user'
] as $n) {
    if (!is_dir($d = $folder . DS . 'lot' . DS . $n)) {
        mkdir($d, 0775, true);
        $log[microtime()] = [
            'status' => 201,
            'description' => i('Created folder %s', ['<code>' . strtr($d, [ROOT => '.']) . '</code>'])
        ];
    } else {
        $log[microtime()] = [
            'status' => 304,
            'description' => i('Folder %s already exists.', ['<code>' . strtr($d, [ROOT => '.']) . '</code>'])
        ];
    }
}

$file = is_file($f = $folder . DS . 'lot' . DS . 'page' . DS . 'blog.page');
if (!$safe || !$file) {
    file_put_contents(Path::F($f) . DS . 'time.data', date('Y-m-d H:i:s'));
    file_put_contents($f, To::page(is([
        'title' => $title = i('Blog'),
        'description' => i('The blog posts.'),
        'type' => 'HTML',
        'chunk' => 20,
        'deep' => 2,
        'sort' => [-1, 'time'],
        'content' => '<p>' . i('Automatically imported from %s.', ['<code>' . $source . '</code>']) . '</p>'
    ], function($v) {
        return isset($v);
    })));
    $log[microtime()] = [
        'status' => 201,
        'description' => i('Created %s page.', ['<strong>' . $title . '</strong>'])
    ];
} else if ($file) {
    $log[microtime()] = [
        'status' => 304,
        'description' => i('File %s already exists.', ['<code>' . strtr($f, [ROOT => '.']) . '</code>'])
    ];
}

$log[microtime()] = [
    'status' => 102,
    'description' => i('Importing blog details...')
];

return [
    'log' => $log,
    'next' => $next ?? $url . '/.import/blogger.v2/task-1' . $url->query
];