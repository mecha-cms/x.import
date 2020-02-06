<?php

return function($f, $data, $query, &$log) {
    if (
        empty($data['content']) ||
        false === strpos($data['content'], '</a>') ||
        false === strpos($data['content'], '</iframe>') ||
        false === strpos($data['content'], '<img ')
    ) {
        return $data;
    }
    $link_0 = $query['url'][0] ?? null;
    $link_1 = $query['url'][1] ?? null;
    $data['content'] = preg_replace_callback('/<(a|iframe|img)(\s[^>]*?)?>/', function($m) use($link_0, $link_1) {
        if (empty($m[2])) {
            return $m[0];
        }
        if ('a' === $m[1] && false !== strpos($m[2], ' href="')) {
            $a = new HTML($m[0]);
            // Remove .html extension from URL
            $a['href'] = preg_replace('/^(' . x($link_0) . '|' . x($link_1) . ')([^?&#]+)\.html([?&#].*)?$/', '$1$2$3', $a['href']);
            $m[0] = $a . "";
        }
        if ($link_0) {
            $m[0] = strtr($m[0], [
                ' href="' . $link_0 . '"' => ' href="/"',
                ' href="' . $link_0 . '/' => ' href="/',
                ' href="' . $link_0 . '?' => ' href="?',
                ' href="' . $link_0 . '&' => ' href="?',
                ' href="' . $link_0 . '#' => ' href="#',
                ' src="' . $link_0 . '"' => ' src="/"',
                ' src="' . $link_0 . '/' => ' src="/',
                ' src="' . $link_0 . '?' => ' src="?',
                ' src="' . $link_0 . '&' => ' src="?',
                ' src="' . $link_0 . '#' => ' src="#',
            ]);
        }
        if ($link_1) {
            $m[0] = strtr($m[0], [
                ' href="' . $link_1 . '"' => ' href="/"',
                ' href="' . $link_1 . '/' => ' href="/',
                ' href="' . $link_1 . '?' => ' href="?',
                ' href="' . $link_1 . '&' => ' href="?',
                ' href="' . $link_1 . '#' => ' href="#',
                ' src="' . $link_1 . '"' => ' src="/"',
                ' src="' . $link_1 . '/' => ' src="/',
                ' src="' . $link_1 . '?' => ' src="?',
                ' src="' . $link_1 . '&' => ' src="?',
                ' src="' . $link_1 . '#' => ' src="#',
            ]);
        }
        return $m[0];
    }, $data['content']);
    return $data;
};
