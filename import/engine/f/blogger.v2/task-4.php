<?php

$index = $query['i'] - 1;
$index = $index < 0 ? 0 : $index;
$index = ($index * $query['chunk']) + 1;
$content = fetch('https://www.blogger.com/feeds/' . $query['blog'] . '/pages/default?alt=json&max-results=' . $query['chunk'] . '&start-index=' . $index);

require __DIR__ . DS . 'f.php';

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
        if (!$safe || !$file) {
            if (!is_dir($d = Path::F($f))) {
                mkdir($d, 0775, true);
            }
            file_put_contents($d . DS . 'blogger.data', json_encode([
                'id' => explode('.page-', $v['id']['$t'], 2)[1]
            ]));
            file_put_contents($d . DS . 'time-create.data', date('Y-m-d H:i:s'));
            if (isset($v['published']['$t'])) {
                file_put_contents($d . DS . 'time.data', date('Y-m-d H:i:s', strtotime($v['published']['$t'])));
            }
            if (isset($v['updated']['$t'])) {
                file_put_contents($d . DS . 'time-update.data', date('Y-m-d H:i:s', strtotime($v['updated']['$t'])));
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
    if ($index + $query['chunk'] >= (int) ($data['feed']['openSearch$totalResults']['$t'] ?? 0)) {
        if ($create > 0) {
            $log[microtime()] = [
                'status' => 201,
                'description' => i('%d page' . (1 === $create ? "" : 's') . ' successfully imported to %s', [$create, '<code>' . strtr($folder . DS . 'lot' . DS . 'page', [ROOT => '.']) . '</code>']) . ' ' . i('Done.')
            ];
        } else {
            $log[microtime()] = [
                'status' => 200,
                'description' => i('Done.')
            ];
        }
    } else {
        $log[microtime()] = [
            'status' => 102,
            'description' => i('Importing next pages...')
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
