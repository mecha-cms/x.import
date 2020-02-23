<?php

$index = $query['i'] - 1;
$index = $index < 0 ? 0 : $index;
$index = ($index * $query['chunk']) + 1;

$fetch = 'https://www.blogger.com/feeds/' . $query['blog'] . '/pages/default?alt=json&max-results=' . $query['chunk'] . '&start-index=' . $index;

if ($r = require __DIR__ . DS . 'f.php') {
    return $r;
}

$create = 0;

if (!empty($data['feed']['entry'])) {
    foreach ($data['feed']['entry'] as $v) {
        $n = uniqid();
        foreach ($v['link'] ?? [] as $vv) {
            if ($source && 'alternate' === $vv['rel']) {
                $n = basename($vv['href'], '.html');
                break;
            }
        }
        $file = is_file($f = $folder . DS . 'lot' . DS . 'page' . DS . $n . '.page');
        $title = $v['title']['$t'] ?? null;
        $content = $v['content']['$t'] ?? null;
        if (empty($query['o']['page'])) {
            $log[microtime()] = [
                'status' => 100,
                'description' => i('Page importer was disabled by the author.')
            ];
        } else if (!$safe || !$file) {
            if (!is_dir($d = Path::F($f))) {
                mkdir($d, 0775, true);
            }
            file_put_contents($d . DS . 'blogger.data', json_encode([
                'id' => explode('.page-', $v['id']['$t'], 2)[1]
            ]));
            file_put_contents($d . DS . 'time-set.data', date('Y-m-d H:i:s'));
            if (isset($v['published']['$t'])) {
                file_put_contents($d . DS . 'time.data', date('Y-m-d H:i:s', strtotime($v['published']['$t'])));
            }
            if (isset($v['updated']['$t'])) {
                file_put_contents($d . DS . 'time-up.data', date('Y-m-d H:i:s', strtotime($v['updated']['$t'])));
            }
            if (!empty($query['f'])) {
                foreach ($query['f'] as $fn) {
                    if ('image' === $fn) {
                        continue; // Continue below
                    }
                    if (!empty($converter[$fn]) && is_callable($converter[$fn])) {
                        $content = call_user_func($converter[$fn], $content);
                    }
                }
            }
            file_put_contents($f, To::page(is([
                'title' => $title,
                'author' => $author && isset($v['author'][0]['name']['$t']) && $author === $v['author'][0]['name']['$t'] ? null : $v['author'][0]['name']['$t'],
                'type' => 'HTML',
                'content' => $content
            ], function($v) {
                return isset($v);
            })));
            $log[microtime()] = [
                'status' => 201,
                'description' => i('Page %s successfully imported to %s', ['<strong>' . ($title ?? basename($f)) . '</strong>', '<code>' . strtr($f, [ROOT => '.']) . '</code>'])
            ];
            ++$create;
        } else if ($file) {
            $log[microtime()] = [
                'status' => 304,
                'description' => i('Page %s already exists.', ['<strong>' . ($title ?? basename($f)) . '</strong>'])
            ];
        }
    }
    $id = md5($post_id = e(explode('.page-', $v['id']['$t'], 2)[1]));
    if (!empty($query['f']['image']) && ($count = substr_count($content, '<img ')) > 0) {
        $log[microtime()] = [
            'status' => 102,
            'description' => i('Contains about %d image' . (1 === $count ? "" : 's') . ' in total.', [$count]) . ' ' . i('Downloading image' . (1 === $count ? "" : 's')) . '…',
            'id' => $id . '-image',
            'next' => $url . '/.import/blogger.v2/task-6' . $url->query('&', [
                'chunk' => false,
                'i' => false,
                'parent' => $id . '-image',
                'target' => strtr($f, [$folder . DS => "", DS => '/'])
            ])
        ];
    }
    if ($index + $query['chunk'] >= (int) ($data['feed']['openSearch$totalResults']['$t'] ?? 0)) {
        if ($create > 0) {
            $log[microtime()] = [
                'status' => 201,
                'description' => i('%d page' . (1 === $create ? "" : 's') . ' successfully imported to %s', [$create, '<code>' . strtr($folder . DS . 'lot' . DS . 'page', [ROOT => '.']) . '</code>'])
            ];
        }
        $log[microtime()] = [
            'status' => 200,
            'description' => i('Done.')
        ];
    } else {
        $log[microtime()] = [
            'status' => 102,
            'description' => i('Importing next pages') . '…'
        ];
        $next = $url . '/.import/blogger.v2/task-4' . $url->query('&', [
            'chunk' => $query['chunk'],
            'i' => $query['i'] + 1
        ]);
    }
} else {
    $log[microtime()] = [
        'status' => 200,
        'description' => i('No more pages to import.') . ' ' . i('Done.')
    ];
}

return [
    'log' => $log,
    'next' => $next ?? true
];
