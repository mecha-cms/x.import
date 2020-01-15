<?php

$path = "";
$next = null;
$query = array_replace([
    'blog' => null, // Blog ID
    'blog-page' => null, // Page ID
    'blog-post' => null, // Blog post ID
    'blog-post-comment' => null, // Blog post comment ID
    'chunk' => 5, // Post(s) per request
    'i' => 1, // Start index
    'key' => null, // API key V3
    'safe' => true,
    'token' => null, // Access token from the Panel
    'type'=> 'blog'
], e($_GET));

if ($id = $query['blog']) {
    $path .= '/blogs' . (true === $id ? "" : '/' . $id);
}

if ($id = $query['blog-post']) {
    $path .= '/posts/' . (true === $id ? "" : '/' . $id);
} else if ($id = $query['blog-page']) {
    $path .= '/pages/' . (true === $id ? "" : '/' . $id);
}

if ($id = $query['blog-post-comment']) {
    $path .= '/comments/' . (true === $id ? "" : '/' . $id);
}

$u = 'https://www.googleapis.com/blogger/v3' . $path . '?fetchBodies=true&fetchImages=true&key=' . $query['key'] . '&maxResults=' . $query['chunk'] . '&orderBy=published' . (isset($query['_pageToken']) ? '&pageToken=' . $query['_pageToken'] : "") . '&status=page';

$content = fetch($u);

$r = [];

if (!$content) {
    $r[] = [
        'status' => 400,
        'description' => i('Error.') . ' (<a href="' . $u . '" target="_blank">?</a>)'
    ];
    return ['status' => $r];
}

$data = json_decode($content, true);

$safe = !empty($query['safe']);
$source = trim($data['url'], '/');

$folder = LOT . DS . '.import' . DS . 'blogger.com' . DS . explode('://', $source, 2)[1];

foreach ([
    'lot/comment',
    'lot/page',
    'lot/page/blog',
    'lot/tag',
    'lot/user'
] as $n) {
    if (!is_dir($d = $folder . DS . $n)) {
        mkdir($d, 0775, true);
    }
}

if (!empty($query['blog-post'])) {
    foreach ($data['items'] as $v) {
        $n = trim(substr($v['url'], strlen($source) + 1, -strlen('.html')), '/');test($n);exit;
        if (!$safe || !is_file($folder . DS . 'blog' . DS . $n)) {}
    }
} else {
    if (empty($query['safe']) || !is_file($f = $folder . DS . 'state.php')) {
        file_put_contents($f, '<?' . 'php return ' . z([
            'description' => $data['description'],
            'id' => $data['id'],
            'title' => $data['name']
        ]) . ';');
        $r[] = [
            'status' => 200,
            'description' => i('Blog data successfully imported to %s', ['<code>' . $f . '</code>'])
        ];
        $r[] = [
            'description' => i('Found %d posts and %d pages in total.', [$data['posts']['totalItems'] ?? 0, $data['pages']['totalItems'] ?? 0])
        ];
        $r[] = [
            'description' => i('Importing blog posts...')
        ];
        $next = $url . '/.import/blogger.com' . $url->query('&', [
            '_pageToken' => $data['nextPageToken'] ?? null,
            'blog' => $query['blog'],
            'blog-post' => 'true',
            'chunk' => $query['chunk'],
            'key' => $query['key']
        ]);
    }
}

return [
    'status' => $r,
    'next' => $next
];
