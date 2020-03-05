<?php

$_['lot']['bar']['lot'][1]['lot']['tools'] = array_replace_recursive($_['lot']['bar']['lot'][1]['lot']['tools'] ?? [], [
    'icon' => 'M21.71 20.29L20.29 21.71A1 1 0 0 1 18.88 21.71L7 9.85A3.81 3.81 0 0 1 6 10A4 4 0 0 1 2.22 4.7L4.76 7.24L5.29 6.71L6.71 5.29L7.24 4.76L4.7 2.22A4 4 0 0 1 10 6A3.81 3.81 0 0 1 9.85 7L21.71 18.88A1 1 0 0 1 21.71 20.29M2.29 18.88A1 1 0 0 0 2.29 20.29L3.71 21.71A1 1 0 0 0 5.12 21.71L10.59 16.25L7.76 13.42M20 2L16 4V6L13.83 8.17L15.83 10.17L18 8H20L22 4Z',
    'lot' => [
        'import' => [
            'icon' => 'M12,3C8.59,3 5.69,4.07 4.54,5.57L9.79,10.82C10.5,10.93 11.22,11 12,11C16.42,11 20,9.21 20,7C20,4.79 16.42,3 12,3M3.92,7.08L2.5,8.5L5,11H0V13H5L2.5,15.5L3.92,16.92L8.84,12M20,9C20,11.21 16.42,13 12,13C11.34,13 10.7,12.95 10.09,12.87L7.62,15.34C8.88,15.75 10.38,16 12,16C16.42,16 20,14.21 20,12M20,14C20,16.21 16.42,18 12,18C9.72,18 7.67,17.5 6.21,16.75L4.53,18.43C5.68,19.93 8.59,21 12,21C16.42,21 20,19.21 20,17',
            'current' => 0 === strpos($_['path'] . '/', '/.import/'),
            'url' => $url . $_['/'] . '/::g::/.import/1',
            'stack' => 20
        ]
    ],
    'link' => "",
    'stack' => 20
]);

if ('g' === $_['task'] && '.import' === $_['path']) {
    $_['lot']['desk']['lot']['form']['lot'][0]['hidden'] = true;
    if (empty($_GET['tool'])) {
        $content = "";
        $content .= '<ul class="import-tools">';
        foreach (g(__DIR__ . DS . '..' . DS . '..' . DS . 'about', 'archive,draft,page') as $k => $v) {
            $kk = new Page($k);
            $content .= '<li>';
            $content .= '<h4>';
            $content .= 'draft' === $kk->x ? $kk->title : '<a href="' . $url->query('&amp;', [
                'tool' => basename($k, '.page')
            ]) . '">' . $kk->title . '</a>';
            $content .= '</h4>';
            $content .= explode('<!-- cut -->', $kk->content)[0];
            $content .= '</li>';
        }
        $content .= '</ul>';
    } else {
        $id = basename(strip_tags($_GET['tool']));
        $page = new Page(__DIR__ . DS . '..' . DS . '..' . DS . 'about' . DS . $id . '.page');
        $lot = is_file($f = __DIR__ . DS . 'state' . DS . $id . '.php') ? (function($f, $page) {
            extract($GLOBALS, EXTR_SKIP);
            return require $f;
        })($f, $page) : [];
        $_['lot']['bar']['lot'][0]['lot']['folder']['hidden'] = true;
        $_['lot']['bar']['lot'][0]['lot']['link']['hidden'] = false;
        $_['lot']['bar']['lot'][0]['lot']['link']['url'] = $url . $url->path . $url->hash;
        $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['info'] = [
            'lot' => [
                'info' => [
                    'title' => $page->title,
                    'type' => 'Section',
                    'content' => $page->content,
                    'stack' => 10
                ]
            ],
            'stack' => 9.91
        ];
        $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['log'] = [
            'content' => '<ul class="code" id="import-log"></ul>',
            'stack' => 9.92
        ];
    }
    $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['tools'] = [
        'title' => 'Tool' . (empty($_GET['tool']) ? 's' : ""),
        'content' => $content ?? null,
        'lot' => $lot ?? [],
        'stack' => 9.9
    ];
}

$GLOBALS['_'] = $_;
