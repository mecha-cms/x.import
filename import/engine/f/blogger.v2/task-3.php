<?php

$index = $query['i'] - 1;
$index = $index < 0 ? 0 : $index;
$index = ($index * $query['chunk']) + 1;

$fetch = 'https://www.blogger.com/feeds/' . $query['blog'] . '/posts/' . (empty($query['o']['post']) ? 'summary' : 'default') . '?alt=json&max-results=' . $query['chunk'] . '&start-index=' . $index;

if ($r = require __DIR__ . DS . 'f.php') {
    return $r;
}

$create = 0;

if (!empty($data['feed']['entry'])) {
    foreach ($data['feed']['entry'] as $v) {
        $n = uniqid();
        foreach ($v['link'] ?? [] as $vv) {
            if ($source && 'alternate' === $vv['rel']) {
                $n = substr(strtr(explode('?', $vv['href'], 2)[0], ['https://' => 'http://']), strlen($source) + 1, -strlen('.html'));
                break;
            }
        }
        $file = is_file($f = $folder . DS . 'lot' . DS . 'page' . $query['folder'] . DS . $n . '.page');
        $title = $v['title']['$t'] ?? null;
        if (empty($query['o']['post'])) {
            $log[microtime()] = [
                'status' => 100,
                'description' => i('Post importer was disabled by the author.')
            ];
        } else if (!$safe || !$file) {
            if (!is_dir($d = Path::F($f))) {
                mkdir($d, 0775, true);
            }
            file_put_contents($d . DS . 'blogger.data', json_encode([
                'id' => explode('.post-', $v['id']['$t'], 2)[1]
            ]));
            file_put_contents($d . DS . 'time-create.data', date('Y-m-d H:i:s'));
            if (isset($v['published']['$t'])) {
                file_put_contents($d . DS . 'time.data', date('Y-m-d H:i:s', strtotime($v['published']['$t'])));
            }
            if (isset($v['updated']['$t'])) {
                file_put_contents($d . DS . 'time-update.data', date('Y-m-d H:i:s', strtotime($v['updated']['$t'])));
            }
            if (!empty($v['category']) && !empty($query['o']['tag'])) {
                $tags = [];
                foreach ($v['category'] as $kk => $vv) {
                    $n = To::kebab($vv['term'] ?? $kk + 1);
                    if (is_file($t = $folder . DS . 'lot' . DS . 'tag' . DS . $n . DS . 'id.data')) {
                        $tags[] = (int) file_get_contents($t);
                    }
                }
                if ($tags) {
                    file_put_contents($d . DS . 'kind.data', json_encode(array_unique($tags)));
                }
            }
            file_put_contents($f, To::page(is([
                'title' => $title,
                'author' => $author && isset($v['author'][0]['name']['$t']) && $author === $v['author'][0]['name']['$t'] ? null : $v['author'][0]['name']['$t'],
                'type' => 'HTML',
                'content' => $v['content']['$t'] ?? null
            ], function($v) {
                return isset($v);
            })));
            $log[microtime()] = [
                'status' => 201,
                'description' => i('Post %s successfully imported to %s', ['<strong>' . ($title ?? basename($f)) . '</strong>', '<code>' . strtr($f, [ROOT => '.']) . '</code>'])
            ];
            ++$create;
        } else if ($file) {
            $log[microtime()] = [
                'status' => 304,
                'description' => i('Post %s already exists.', ['<strong>' . ($title ?? basename($f)) . '</strong>'])
            ];
        }
        $count = (int) ($v['thr$total']['$t'] ?? 0);
        if ($count > 0) {
            $id = e(explode('.post-', $v['id']['$t'], 2)[1]);
            $log[microtime()] = [
                'status' =>102,
                'description' => i('Found %d comment' . (1 === $count ? "" : 's') . ' in total.', [$count]) . ' ' . i('Importing comments...'),
                'id' => md5($id),
                'next' => $url . '/.import/blogger.v2/task-5' . $url->query('&', [
                    'chunk' => $query['chunk'],
                    'current' => $query['i'],
                    'i' => 1,
                    'parent' => $id
                ])
            ];
        }
    }
    if ($index + $query['chunk'] >= (int) ($data['feed']['openSearch$totalResults']['$t'] ?? 0)) {
        if ($create > 0) {
            if (!empty($query['o']['post'])) {
                $log[microtime()] = [
                    'status' => 201,
                    'description' => i('%d post' . (1 === $create ? "" : 's') . ' successfully imported to %s', [$create, '<code>' . strtr($folder . DS . 'lot' . DS . 'page' . $query['folder'], [ROOT => '.']) . '</code>'])
                ];
            }
        } else {
            $log[microtime()] = [
                'status' => 100,
                'description' => i('Continue') . '…'
            ];
        }
        $log[microtime()] = [
            'status' => 102,
            'description' => i('Importing blog pages') . '…'
        ];
        $next = $url . '/.import/blogger.v2/task-4' . $url->query('&', [
            'chunk' => $query['chunk'],
            'i' => 1
        ]);
    } else {
        $log[microtime()] = [
            'status' => 102,
            'description' => i('Importing next posts') . '…'
        ];
        $next = $url . '/.import/blogger.v2/task-3' . $url->query('&', [
            'chunk' => $query['chunk'],
            'i' => $query['i'] + 1
        ]);
    }
} else {
    $log[microtime()] = [
        'status' => 200,
        'description' => i('No more posts to import.') . ' ' . i('Importing blog pages') . (empty($query['o']['page']) ? ' (' . i('disabled') . ')' : '…')
    ];
    $next = $url . '/.import/blogger.v2/task-4' . $url->query('&', [
        'chunk' => $query['chunk'],
        'i' => 1
    ]);
}

return [
    'log' => $log,
    'next' => $next ?? null
];
