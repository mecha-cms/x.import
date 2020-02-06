<?php

return function($f, $data, $query) {
    if (
        empty($data['content']) ||
        false === strpos($data['content'], '<img ')
    ) {
        return $data;
    }
    $data['content'] = preg_replace_callback('/<img(\s[^>].*?)?>/', function($m) use($f, $query) {
        if (false === strpos($m[0], ' src="')) {
            return $m[0];
        }
        $img = new HTML($m[0]);
        if ($img['src'] && $src = fetch($img['src'], 'Mecha/' . VERSION . ' (+https://mecha-cms.com)')) {
            $f = strtr(Path::F($f), [
                DS . 'lot' . DS . 'page' . DS => DS . 'lot' . DS . 'asset' . DS . Path::X($img['src']) . DS
            ]) . DS . To::file(basename($img['src']));
            if (!is_dir($d = dirname($f))) {
                mkdir($d, 0775, true);
            }
            file_put_contents($f, $src);
            $href = To::URL($f);
            $img['src'] = empty($query['f']['link']) ? $href : strtr($href, [$GLOBALS['url'] . "" => ""]);
            $m[0] = $img . "";
        }
        return $m[0];
    }, $data['content']);
    return $data;
};
