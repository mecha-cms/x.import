<?php

$fetch = 'https://www.blogger.com/feeds/' . $query['blog'] . '/posts/summary?alt=json&max-results=0&start-index=1';

if ($r = require __DIR__ . DS . 'f.php') {
    return $r;
}

$id = $query['parent'];

if (empty($query['target'])) {
    $log[microtime()] = [
        'status' => 408,
        'description' => i('Missing %s parameter.', ['<code>target</code>']),
        'parent' => $id
    ];
    return [
        'log' => $log,
        'next' => false
    ];
}

if (!is_file($f = $folder . DS . strtr($query['target'], '/', DS))) {
    $log[microtime()] = [
        'status' => 404,
        'description' => i('File %s does not exists.', ['<code>' . strtr($f, [ROOT => '.']) . '</code>']),
        'parent' => $id
    ];
    return [
        'log' => $log,
        'next' => false
    ];
}

$data = From::page(file_get_contents($f), true);

if (empty($data['content']) || false === strpos($data['content'], '<img ')) {
    $log[microtime()] = [
        'status' => 201,
        'description' => 'No images found.',
        'parent' => $id
    ];
    return [
        'log' => $log,
        'next' => true
    ];
}

// TODO: Download image URL in anchor tag?
$data['content'] = preg_replace_callback('/<img(\s[^>]*?)?>/', function($m) use($f, $folder, $id, &$log, $query, $url) {
    if (empty($m[1]) || false === strpos($m[1], ' src="')) {
        return $m[0];
    }
    $img = new HTML($m[0]);
    $src = $img['src'];
    $f = strtr(Path::F($f), [
        DS . 'lot' . DS . 'page' . DS => DS . 'lot' . DS . 'asset' . DS . Path::X($src) . DS
    ]) . DS . To::file(basename($src));
    if (is_file($f)) {
        $log[microtime()] = [
            'status' => 304,
            'description' => i('File %s already exists.', ['<code>' . strtr($f, [ROOT => '.']) . '</code>']),
            'parent' => $id
        ];
    } else if ($blob = $src ? fetch(URL::long($src)) : null) {
        if (!is_dir($d = dirname($f))) {
            mkdir($d, 0775, true);
        }
        file_put_contents($f, $blob);
        $log[microtime()] = [
            'status' => 201,
            'description' => i('Image %s successfully downloaded and saved to %s.', ['<code>' . basename($src) . '</code>', '<code>' . strtr($f, [ROOT => '.']) . '</code>']),
            'parent' => $id
        ];
    }
    $href = To::URL($f);
    $img['src'] = strtr($href, [To::URL($folder) => ""]);
    $m[0] = $img . "";
    return $m[0];
}, $data['content']);

$log[microtime()] = [
    'status' => 100,
    'description' => i('Updating target file') . '…',
    'parent' => $id
];

file_put_contents($f, To::page($data));

$log[microtime()] = [
    'status' => 200,
    'description' => i('Done.'),
    'parent' => $id
];

return [
    'log' => $log,
    'next' => true
];
