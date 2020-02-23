<?php

$query['folder'] = strtr($query['folder'] ?? '/' . uniqid(), '/', DS);

if (empty($query['blog'])) {
    $log[microtime()] = [
        'status' => 408,
        'description' => i('Missing blog ID.')
    ];
    return [
        'log' => $log,
        'next' => false
    ];
}

$safe = array_key_exists('safe', $query);

$log = [];

$content = Cache::live($fetch, function() use($fetch) {
    return !empty($fetch) ? @fetch($fetch) : '{}';
}, '1 month');

if (!$content) {
    $log[microtime()] = [
        'status' => 408,
        'description' => i(error_get_last()['message'] ?? 'Error.')
    ];
    return [
        'log' => $log,
        'next' => false
    ];
}

$data = json_decode($content, true);
$source = null;

$author = $data['feed']['author'][0]['name']['$t'] ?? null;

foreach ($data['feed']['link'] ?? [] as $v) {
    if ('alternate' === $v['rel']) {
        // Normalize from `https://example.com` to `http://example.com`
        $source = strtr(trim($v['href'], '/'), ['https://' => 'http://']);
        break;
    }
}

if ($source) {
    // Normalize from `http://www.example.com` to `http://example.com`
    $source = preg_replace('/^http:\/\/www\./', 'http://', $source);
    // Normalize from `http://example.blogspot.*` to `http://example.blogspot.com`
    $source = preg_replace('/\.blogspot\.\S+$/', '.blogspot.com', $source);
}

$host = explode('://', $source, 2)[1] ?? $query['id'];

$folder = $safe ? LOT . DS . '.import' . DS . 'blogger.com' . DS . $host : ROOT;

$converter = [
    'h-t-m-l' => function($content) {
        if (!$content) {
            return $content;
        }
        if (false !== strpos($content, '/>')) {
            $content = preg_replace('/<(hr|img|input)(\s[^>]*)? *\/?>/', '<$1$2>', $content);
        }
        $content = strtr($content, [
            '<b>' => '<strong>',
            '<i>' => '<em>',
            '</b>' => '</strong>',
            '</i>' => '</em>'
        ]);
        return $content;
    }
];

return; // Return `null` on success
