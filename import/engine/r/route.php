<?php

if ($url->path === '/panel/::g::/.import') {
    $GLOBALS['_']['lot']['desk']['lot']['form']['lot'][0]['hidden'] = true;
    Hook::set('get', function() use($url) {
        if (empty($_GET['tool'])) {
            $content = "";
            $content .= '<ul class="import-tools">';
            foreach (g(__DIR__ . DS . '..' . DS . '..' . DS . 'about', 'archive,draft,page') as $k => $v) {
                $kk = new Page($k);
                $content .= '<li>';
                $content .= '<h4><a href="' . $url->query('&amp;', [
                    'tool' => basename($k, '.page')
                ]) . '">' . $kk->title . '</a></h4>';
                $content .= $kk->content;
                $content .= '</li>';
            }
            $content .= '</ul>';
        } else {
            $id = basename(strip_tags($_GET['tool']));
            if (is_file($f = __DIR__ . DS . '..' . DS . 'f' . DS . $id . DS . 'session@' . Cookie::get('user.key') . '.php')) {
                $_SESSION['form'] = (require $f)[2];
            }
            $lot = is_file($f = __DIR__ . DS . 'state' . DS . $id . '.php') ? (function($f, $page) {
                extract($GLOBALS, EXTR_SKIP);
                return require $f;
            })($f, $page = new Page(__DIR__ . DS . '..' . DS . '..' . DS . 'about' . DS . $id . '.page')) : [];
        }
        $GLOBALS['_']['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['tools'] = [
            'title' => 'Tool' . (empty($_GET['tool']) ? 's' : ""),
            'content' => $content ?? null,
            'lot' => $lot ?? [],
            'stack' => 9.9
        ];
    }, 0);

    // '<p><a class="button" data-loading="Initializing the importer..." href="http://127.0.0.1/mecha/.import/blogger.v2/task-0?blog=298900102869691923" id="import-link">Import</a></p><p id="import-meta"></p><ul id="import-log" tabindex="0"></ul>'
}

Route::set('.import/:service/:task', 200, function($service, $task) {
    $this->type('application/json');
    if (is_file($f = __DIR__ . DS . '..' . DS . 'f' . DS . $service . DS . $task . '.php')) {
        $query = array_replace([
            'blog' => null, // Blog ID
            'chunk' => 10,
            'folder' => '/blog',
            'i' => 1, // Start index
            'id' => uniqid(),
            'safe' => true,
            'token' => null // Access token from the Panel
        ], e($_GET));
        $content = '<?' . 'php return ' . z([time(), $service . '/' . $task, $query]) . ';';
        file_put_contents(dirname($f) . DS . 'session@' . Cookie::get('user.key') . '.php', $content);
        $this->content(json_encode((function($f) use($query) {
            extract($GLOBALS, EXTR_SKIP);
            /*if (!$query['token'] || !Guard::check($query['token'], 'import')) {
                return [
                    'log' => [
                        [
                            'status' => 401,
                            'description' => 'Invalid token.'
                        ]
                    ],
                    'next' => false
                ];
            }*/
            return require $f;
        })($f), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
    }
    $this->status(404);
    $time = microtime();
    $this->content(json_encode([$time => [
        'status' => 400,
        'description' => i('Service unavailable.')
    ]], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
}, 0);
