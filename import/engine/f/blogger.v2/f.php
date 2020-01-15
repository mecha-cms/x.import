<?php

$safe = !empty($query['safe']);

$log = [];

if (!$content) {
    $log[microtime()] = [
        'status' => 408,
        'description' => i('Error.')
    ];
    return [
        'log' => $log,
        'next' => false
    ];
}

$data = json_decode($content, true);
$source = null;

$author = $data['feed']['author'][0]['name']['$t'] ?? null;

foreach ($data['feed']['link'] as $v) {
    if ('alternate' === $v['rel']) {
        $source = strtr(trim($v['href'], '/'), ['https://', 'http://']);
        break;
    }
}

$host = preg_replace('/^www\./', "", explode('://', $source, 2)[1] ?? $query['id']);

$folder = $safe ? LOT . DS . '.import' . DS . 'blogger.com' . DS . $host : ROOT;
