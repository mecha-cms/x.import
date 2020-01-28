<?php

return [
    'form' => [
        'type' => 'Form.Get',
        'url' => $url . '/.import/' . basename(__FILE__, '.php') . '/task-0',
        '2' => [
            'id' => 'import-form',
            'target' => '_blank'
        ],
        'lot' => [
            'fields' => [
                'title' => $page->title,
                'description' => $page->description,
                'type' => 'Fields',
                'lot' => [
                    'blog' => [
                        'title' => 'Blog ID',
                        'description' => ['Your %s blog ID.', ['<strong>Blogger</strong>']],
                        'type' => 'Text',
                        'pattern' => "^\d+$",
                        'alt' => crc32($_SERVER['REQUEST_TIME']),
                        'stack' => 10
                    ],
                    'folder' => [
                        'description' => ['Folder path relative to %s to store the blog posts.', ['<code>' . strtr(LOT . DS . 'page', [ROOT => '.']) . '</code>']],
                        'type' => 'Text',
                        'pattern' => "^[\\\\/][a-z\\d]+([_.-][a-z\\d]+)*([\\\\/][a-z\\d]+([_.-][a-z\\d]+)*)*$",
                        'alt' => '/article',
                        'value' => '/blog',
                        'stack' => 20
                    ],
                    'url[0]' => [
                        'title' => 'Base URL 1',
                        'description' => ['Base URL of your %s domain.', ['<strong>Blogger</strong>']],
                        'before' => 'http://',
                        'type' => 'Text',
                        'pattern' => "^[^.\\s]+\\.blogspot(?:\\.[^.\\s]+)+$",
                        'alt' => S . i('example') . '.blogspot.com' . S,
                        'stack' => 30
                    ],
                    'url[1]' => [
                        'title' => 'Base URL 2',
                        'description' => 'Base URL of your top level domain if any.',
                        'before' => 'http://',
                        'type' => 'Text',
                        'pattern' => "^[^.\\s]+(?:\\.[^.\\s]+)+$",
                        'alt' => S . i('example') . '.com' . S,
                        'stack' => 31
                    ],
                    'o' => [
                        'title' => 'Options',
                        'type' => 'Items',
                        'block' => true,
                        'sort' => false,
                        'lot' => [
                            'post' => 'Import blog posts.',
                            'tag' => 'Import blog tags.',
                            'comment' => 'Import blog comments.',
                            'page' => 'Import blog static pages.'
                        ],
                        'value' => [
                            'post' => 1,
                            'tag' => 1,
                            'page' => 1
                        ],
                        'stack' => 40
                    ],
                    'f' => [
                        'title' => 'Filters',
                        'type' => 'Items',
                        'block' => true,
                        'lot' => [
                            'link' => [
                                'title' => 'Automatically convert old internal URL into relative URL.',
                                'frozen' => true
                            ],
                            'p' => [
                                'active' => $ok = null !== State::get('x.p'),
                                'title' => 'Convert line-break sequence into paragraph sequence.',
                                'description' => $ok ? null : 'Missing automatic paragraph extension.'
                            ],
                            'image' => 'Download blog post\'s images and convert post\'s image URL into local image URL.'
                        ],
                        'value' => [
                            'link' => 1
                        ],
                        'stack' => 50
                    ]
                ],
                'stack' => 10
            ],
            'log' => [
                'type' => 'Fields',
                'lot' => [
                    0 => [
                        'title' => "",
                        'type' => 'Field',
                        'content' => '<ul id="import-log"></ul>',
                        'stack' => 10
                    ]
                ]
            ],
            'tasks' => [
                'type' => 'Fields',
                'tags' => ['mt:2'],
                'lot' => [
                    0 => [
                        'title' => "",
                        'type' => 'Field',
                        'lot' => [
                            'tasks' => [
                                'type' => 'Tasks.Button',
                                'lot' => [
                                    'g' => [
                                        'title' => 'Import',
                                        'type' => 'Submit',
                                        'name' => false,
                                        'stack' => 10
                                    ]
                                ],
                                'stack' => 10
                            ]
                        ]
                    ]
                ],
                'stack' => 30
            ]
        ]
    ]
];
