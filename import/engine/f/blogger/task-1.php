<?php

$fetch = 'https://www.blogger.com/feeds/' . $query['blog'] . '/posts/summary?alt=json&max-results=0&start-index=1';

if ($r = require __DIR__ . DS . 'f.php') {
    return $r;
}

$file = is_file($f = $folder . DS . 'state.php');
if (!$safe || !$file) {
    file_put_contents($f, '<?' . 'php return ' . z([
        'charset' => strtolower($data['encoding'] ?? 'utf-8'),
        'description' => $data['feed']['subtitle']['$t'],
        'direction' => 'ltr',
        'id' => substr($data['feed']['id']['$t'], strpos($data['feed']['id']['$t'], 'blog-') + 5),
        'language' => $state->language ?? 'en',
        'path' => strtr($query['folder'], DS, '/'),
        'title' => $data['feed']['title']['$t'],
        'x' => [
            'page' => [
                'page' => [
                    'author' => $author ? '@' . To::kebab($author) : null,
                    'type' => 'HTML'
                ]
            ]
        ]
    ]) . ';');
    $log[microtime()] = [
        'status' => 201,
        'description' => i('Blog details successfully imported to %s', ['<code>' . strtr($f, [ROOT => '.']) . '</code>'])
    ];
} else if ($file) {
    $log[microtime()] = [
        'status' => 304,
        'description' => i('File %s already exists.', ['<code>' . strtr($f, [ROOT => '.']) . '</code>'])
    ];
}

$log[microtime()] = [
    'status' => 102,
    'description' => i('Importing blog tags') . '…'
];

return [
    'log' => $log,
    'next' => $next ?? $url . '/.import/blogger/task-2' . $url->query
];
