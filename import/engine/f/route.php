<?php

namespace _\lot\x\panel\route {
    function __import($_, $lot) {
        if ('g' !== $_['task']) {
            return;
        }
        extract($GLOBALS, \EXTR_SKIP);
        $_['lot']['desk']['lot']['form']['lot'][0]['hidden'] = true;
        if (empty($lot['tool'])) {
            $content = "";
            $content .= '<ul class="import-tools">';
            foreach (\g(__DIR__ . \DS . '..' . \DS . '..' . \DS . 'about', 'archive,draft,page') as $k => $v) {
                $kk = new \Page($k);
                $content .= '<li>';
                $content .= '<h4>';
                $content .= 'draft' === $kk->x ? $kk->title : '<a href="' . $url->query('&amp;', [
                    'tool' => \basename($k, '.page')
                ]) . '">' . $kk->title . '</a>';
                $content .= '</h4>';
                $content .= \_\lot\x\panel\h\description(['description' => $kk->description]);
                $content .= \explode('<!-- cut -->', $kk->content)[0];
                $content .= '</li>';
            }
            $content .= '</ul>';
        } else {
            $id = \basename(\strip_tags($lot['tool']));
            $page = new \Page(__DIR__ . \DS . '..' . \DS . '..' . \DS . 'about' . \DS . $id . '.page');
            $fields = \is_file($f = __DIR__ . \DS . '..' . \DS . 'r' . \DS . 'state' . \DS . $id . '.php') ? (function($f, $page) {
                extract($GLOBALS, \EXTR_SKIP);
                return require $f;
            })($f, $page) : [];
            $_['lot']['bar']['lot'][0]['lot']['folder']['hidden'] = true;
            $_['lot']['bar']['lot'][0]['lot']['link']['hidden'] = false;
            $_['lot']['bar']['lot'][0]['lot']['link']['url'] = $url . $url->path . $url->hash;
            $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['info'] = [
                'lot' => [
                    'info' => [
                        'title' => $page->title,
                        'description' => $page->description,
                        'type' => 'Section',
                        'content' => $page->content,
                        'stack' => 10
                    ]
                ],
                'stack' => 9.91
            ];
            $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['log'] = [
                'content' => '<ul class="code import-log"></ul>',
                'stack' => 9.92
            ];
        }
        $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['tools'] = [
            'title' => 'Tool' . (empty($lot['tool']) ? 's' : ""),
            'content' => $content ?? null,
            'lot' => $fields ?? [],
            'stack' => 9.9
        ];
        // Update data
        $GLOBALS['_'] = $_;
    }
}
