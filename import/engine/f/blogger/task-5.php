<?php

$index = $query['i'] - 1;
$index = $index < 0 ? 0 : $index;
$index = ($index * $query['chunk']) + 1;

$fetch = 'https://www.blogger.com/feeds/' . $query['blog'] . '/' . $query['id'] . '/comments/default?alt=json&max-results=' . $query['chunk'] . '&start-index=' . $index;

if ($r = require __DIR__ . DS . 'f.php') {
    return $r;
}

$id = $query['parent'];

$create = 0;

if ($source) {
    $u = parse_url($source);
    $source = 'http://' . $u['host'];
    $host = preg_replace('/^www\./', "", explode('://', $source, 2)[1] ?? $query['id']);
    $folder = $safe ? LOT . DS . '.import' . DS . 'blogger.com' . DS . $host : ROOT;
}

$n = uniqid();

foreach ($data['feed']['link'] ?? [] as $vv) {
    if ($source && 'alternate' === $vv['rel']) {
        $n = substr(strtr(explode('?', $vv['href'], 2)[0], ['https://' => 'http://']), strlen($source) + 1, -strlen('.html'));
        break;
    }
}

if (!empty($data['feed']['entry'])) {
    foreach ($data['feed']['entry'] as $v) {
        $file = is_file($f = $folder . DS . 'lot' . DS . 'comment' . $query['folder'] . DS . $n . DS . date('Y-m-d-H-i-s', strtotime($v['published']['$t'])) . '.page');
        $title = strip_tags($v['title']['$t'] ?? "") ?: null;
        $content = $v['content']['$t'] ?? null;
        $self = explode('.post-', $v['id']['$t'], 2)[1];
        $parent = null;
        $parent_feed = null;
        if (!$safe || !$file) {
            if (!is_dir($d = Path::F($f))) {
                mkdir($d, 0775, true);
            }
            foreach ($v['link'] as $vv) {
                if ('related' === $vv['rel']) {
                    $a = explode('/', $parent_feed = $vv['href']);
                    $parent = end($a);
                    break;
                }
            }
            if ($parent && $parent_data = fetch(strtr($parent_feed, ['/default' => '/summary']) . '?alt=json')) {
                $parent_data = json_decode($parent_data, true);
                if (!empty($parent_data['entry']['published']['$t'])) {
                    file_put_contents($d . DS . 'parent.data', date('Y-m-d-H-i-s', strtotime($parent_data['entry']['published']['$t'])));
                }
            }
            // file_put_contents($d . DS . 'blogger.data', json_encode([
            //     'parent' => $parent,
            //     'self' => $self
            // ]));
            // file_put_contents($d . DS . 'time-set.data', date('Y-m-d H:i:s'));
            // if (isset($v['published']['$t'])) {
            //     file_put_contents($d . DS . 'time.data', date('Y-m-d H:i:s', strtotime($v['published']['$t'])));
            // }
            if (isset($v['updated']['$t'])) {
                file_put_contents($d . DS . 'time-up.data', date('Y-m-d H:i:s', strtotime($v['updated']['$t'])));
            }
            if (!empty($query['f'])) {
                foreach ($query['f'] as $fn => $foo) {
                    if ('image' === $fn) {
                        continue; // Continue below
                    }
                    if (!empty($converter[$fn]) && is_callable($converter[$fn])) {
                        $content = call_user_func($converter[$fn], $content);
                    }
                }
            }
            $avatar = preg_replace('/\/s\d+(\-c)?\//', '/s80-c/', $v['author'][0]['gd$image']['src'] ?? "");
            $avatar = preg_replace('/=s\d+(\-c)?$/', '=s80-c', trim($avatar));
            file_put_contents($f, To::page(is([
                'author' => $author && isset($v['author'][0]['name']['$t']) && $author === $v['author'][0]['name']['$t'] ? '@' . To::kebab($author) : ($v['author'][0]['name']['$t'] ?? null),
                'avatar' => "" !== $avatar ? $avatar : null,
                'status' => $author && isset($v['author'][0]['name']['$t']) && $author === $v['author'][0]['name']['$t'] ? 1 : 2,
                'link' => $v['author'][0]['uri']['$t'] ?? null,
                'type' => 'HTML',
                'content' => $content
            ], function($v) {
                return isset($v);
            })));
            $log[microtime()] = [
                'status' => 201,
                'description' => i('Comment %s successfully imported to %s', ['<strong>' . ($title ?? basename($f)) . '</strong>', '<code>' . strtr($f, [ROOT => '.']) . '</code>']),
                'parent' => $id
            ];
            ++$create;
        } else if ($file) {
            $log[microtime()] = [
                'status' => 304,
                'description' => i('Comment %s already exists.', ['<strong>' . ($title ?? basename($f)) . '</strong>']),
                'parent' => $id
            ];
        }
    }
    if ($index + $query['chunk'] >= (int) ($data['feed']['openSearch$totalResults']['$t'] ?? 0)) {
        $log[microtime()] = [
            'status' => 200,
            'description' => i('Done.'),
            'parent' => $id
        ];
        $next = true;
    } else {
        $log[microtime()] = [
            'status' => 102,
            'description' => i('Importing next comments') . '…',
            'parent' => $id
        ];
        $next = $url . '/.import/blogger/task-5' . $url->query('&', [
            'chunk' => $query['chunk'],
            'i' => $query['i'] + 1
        ]);
    }
} else {
    if ($create > 0) {
        $log[microtime()] = [
            'status' => 201,
            'description' => i('%d comment' . (1 === $create ? "" : 's') . ' successfully imported to %s', [$create, '<code>' . strtr($folder . DS . 'lot' . DS . 'comment' . $query['folder'] . DS . $n, [ROOT => '.']) . '</code>']),
            'parent' => $id
        ];
    }
}

return [
    'log' => $log,
    'next' => $next ?? null
];
