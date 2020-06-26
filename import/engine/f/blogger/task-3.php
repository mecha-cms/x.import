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
                // Normalize from `https://example.com` to `http://example.com`
                $href = strtr(trim($vv['href'], '/'), ['https://' => 'http://']);
                // Normalize from `http://www.example.com` to `http://example.com`
                $href = preg_replace('/^http:\/\/www\./', 'http://', $href);
                // Normalize from `http://example.blogspot.*` to `http://example.blogspot.com`
                $href = preg_replace('/\.blogspot\.[^\s\/]+(.*)$/', '.blogspot.com$1', $href);
                $n = substr(explode('?', $href, 2)[0], strlen($source) + 1, -strlen('.html'));
                break;
            }
        }
        $count = count(explode('/', $n));
        foreach (array_values(step($n, '/')) as $kkk => $vvv) {
            if (0 === $kkk) {
                continue;
            }
            $file = is_file($f = $folder . DS . 'lot' . DS . 'page' . $query['folder'] . DS . $vvv . '.archive');
            if (!$safe || !$file) {
                if (!is_dir($d = Path::F($f))) {
                    mkdir($d, 0775, true);
                }
                $title = basename($vvv);
                file_put_contents($f, To::page([
                    'title' => $title,
                    'deep' => $count - $kkk
                ]));
                $log[microtime()] = [
                    'status' => 201,
                    'description' => i('Created archive %s', ['<strong>' . $title . '</strong>'])
                ];
            } else if ($file) {
                // $log[microtime()] = [
                //     'status' => 304,
                //     'description' => i('File %s already exists.', ['<code>' . strtr($f, [ROOT => '.']) . '</code>'])
                // ];
            }
        }
        $file = is_file($f = $folder . DS . 'lot' . DS . 'page' . $query['folder'] . DS . $n . '.page');
        $title = $v['title']['$t'] ?? null;
        $content = $v['content']['$t'] ?? null;
        $self = explode('.post-', $v['id']['$t'], 2)[1];
        if (empty($query['o']['post'])) {
            $log[microtime()] = [
                'status' => 100,
                'description' => i('Post importer was disabled by the author.')
            ];
        } else if (!$safe || !$file) {
            if (!is_dir($d = Path::F($f))) {
                mkdir($d, 0775, true);
            }
            // file_put_contents($d . DS . 'blogger.data', json_encode([
            //     'self' => $self
            // ]));
            // file_put_contents($d . DS . 'time-set.data', date('Y-m-d H:i:s'));
            if (isset($v['published']['$t'])) {
                file_put_contents($d . DS . 'time.data', date('Y-m-d H:i:s', strtotime($v['published']['$t'])));
            }
            if (isset($v['updated']['$t'])) {
                file_put_contents($d . DS . 'time-up.data', date('Y-m-d H:i:s', strtotime($v['updated']['$t'])));
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
            if (!empty($query['f'])) {
                foreach ($query['f'] as $fn => $foo) {
                    if ('image' === $fn) {
                        continue; // Continue below
                    }
                    if (!empty($converter[$fn]) && is_callable($converter[$fn])) {
                        $out = call_user_func($converter[$fn], $content);
                        $content = $out[0];
                        if ('link' === $fn && !empty($out[1])) {
                            // TODO: Store link(s) to kick.tsv
                            foreach ($out[1] as $kk => $vv) {

                            }
                        }
                    }
                }
            }
            file_put_contents($f, To::page(is([
                'title' => $title,
                'author' => $author && isset($v['author'][0]['name']['$t']) && $author === $v['author'][0]['name']['$t'] ? null : ($v['author'][0]['name']['$t'] ?? null),
                'type' => 'HTML',
                'content' => $content
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
        $id = md5($post_id = e(explode('.post-', $v['id']['$t'], 2)[1]));
        if (!empty($query['f']['image']) && ($count = substr_count($content, '<img ')) > 0) {
            $log[microtime()] = [
                'status' => 102,
                'description' => i('Contains about %d image' . (1 === $count ? "" : 's') . ' in total.', [$count]) . ' ' . i('Downloading image' . (1 === $count ? "" : 's')) . '…',
                'id' => $id . '-image',
                'next' => $url . '/.import/blogger/task-6' . $url->query('&', [
                    'chunk' => false,
                    'i' => false,
                    'parent' => $id . '-image',
                    'target' => strtr($f, [$folder . DS => "", DS => '/'])
                ])
            ];
        }
        if (!empty($query['o']['comment'])) {
            $count = (int) ($v['thr$total']['$t'] ?? 0);
            if ($count > 0) {
                $log[microtime()] = [
                    'status' =>102,
                    'description' => i('Found %d comment' . (1 === $count ? "" : 's') . ' in total.', [$count]) . ' ' . i('Importing comments') . '…',
                    'id' => $id . '-comment',
                    'next' => $url . '/.import/blogger/task-5' . $url->query('&', [
                        'chunk' => $query['chunk'],
                        'i' => 1,
                        'id' => $post_id,
                        'parent' => $id . '-comment'
                    ])
                ];
            }
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
        $next = $url . '/.import/blogger/task-4' . $url->query('&', [
            'chunk' => $query['chunk'],
            'i' => 1
        ]);
    } else {
        $log[microtime()] = [
            'status' => 102,
            'description' => i('Importing next posts') . '…'
        ];
        $next = $url . '/.import/blogger/task-3' . $url->query('&', [
            'chunk' => $query['chunk'],
            'i' => $query['i'] + 1
        ]);
    }
} else {
    $log[microtime()] = [
        'status' => 200,
        'description' => i('No more posts to import.') . ' ' . i('Importing blog pages') . (empty($query['o']['page']) ? ' (' . i('disabled') . ')' : '…')
    ];
    $next = $url . '/.import/blogger/task-4' . $url->query('&', [
        'chunk' => $query['chunk'],
        'i' => 1
    ]);
}

return [
    'log' => $log,
    'next' => $next ?? null
];
