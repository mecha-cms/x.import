<?php







if ($url->path === '/panel/::g::/.import') {
    Hook::set('get', function() {
        Asset::set(__DIR__ . DS . 'lot' . DS . 'asset' . DS . 'css' . DS . 'import.css');
        Asset::set(__DIR__ . DS . 'lot' . DS . 'asset' . DS . 'js' . DS . 'import.js');
    }, 20.1);
    $GLOBALS['_']['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['tools'] = [
        'content' => '<p><a class="button" data-loading="Initializing the importer..." href="http://127.0.0.1/mecha/.import/blogger.v2/task-0?blog=461388621809613953" id="import-link">Import</a></p><p id="import-meta"></p><ul id="import-log" tabindex="0"></ul>',
        'stack' => 9.9
    ];
}

Route::set('.import/:service/:task', 200, function($service, $task) {
    $this->type('application/json');
    if (is_file($f = __DIR__ . DS . 'engine' . DS . 'f' . DS . $service . DS . $task . '.php')) {
        $this->content(json_encode((function($f) {
            extract($GLOBALS, EXTR_SKIP);
            $query = array_replace([
                'blog' => null, // Blog ID
                'chunk' => 10,
                'i' => 1, // Start index
                'id' => uniqid(),
                'safe' => true,
                'token' => null // Access token from the Panel
            ], e($_GET));
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
