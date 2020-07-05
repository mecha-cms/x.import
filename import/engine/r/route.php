<?php

Route::set('.import/:service/:task', 200, function($service, $task) {
    $this->type('application/json');
    if (is_file($f = __DIR__ . DS . '..' . DS . 'f' . DS . $service . DS . $task . '.php')) {
        $query = array_replace([
            'blog' => null, // Blog ID
            'chunk' => 50,
            'folder' => '/blog',
            'i' => 1, // Start index
            'id' => uniqid(),
            'safe' => true,
            'token' => null // Access token from the Panel
        ], e($_GET));
        $this->content(json_encode((function($f) use($query) {
            extract($GLOBALS, EXTR_SKIP);
            if (empty($query['is']['author'])) {
                $time = microtime();
                return [
                    'log' => [
                        $time => [
                            'status' => 401,
                            'description' => i('Please state that you are the original author of the blog.')
                        ]
                    ]
                ];
            }
            if (!$query['token'] || !Guard::check($query['token'], 'import')) {
                return [
                    'log' => [
                        microtime() => [
                            'status' => 401,
                            'description' => i('Invalid token.')
                        ]
                    ],
                    'next' => false
                ];
            }
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
