<?php

return [
    'form' => [
        'type' => 'Form.Get',
        'url' => $url . '/.import/' . basename(__FILE__, '.php') . '/task-0',
        '2' => [
            'data-loading' => i('Initializing the importer') . '…',
            'id' => 'import-form',
            'target' => '_blank'
        ],
        'lot' => [
            'fields' => [
                'title' => $page->title,
                'description' => $page->description,
                'type' => 'Fields',
                'lot' => [
                    'token' => [
                         'type' => 'Hidden',
                         'value' => Guard::token('import')
                    ],
                    'blog' => [
                        'title' => 'Blog ID',
                        'description' => ['Your %s blog ID.', ['Blogger']],
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
                        'description' => ['Base URL of your %s domain.', ['Blogger']],
                        'before' => 'http://',
                        'type' => 'Text',
                        'pattern' => "^[^.\\s]+\\.blogspot(?:\\.[^.\\s]+)+$",
                        'alt' => S . i('example') . '.blogspot.com' . S,
                        'stack' => 30
                    ],
                    'url[1]' => [
                        'title' => 'Base URL 2',
                        'description' => 'Base URL of your top level domain (if any).',
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
                            'post' => [
                                'title' => 'Import posts.',
                                'value' => 1
                            ],
                            'page' => [
                                'title' => 'Import pages.',
                                'value' => 1
                            ],
                            'tag' => [
                                'title' => 'Import tags.',
                                'value' => 1
                            ],
                            'comment' => [
                                'title' => 'Import comments.',
                                'value' => 1
                            ]
                        ],
                        'value' => [
                            'post' => 1,
                            'page' => 1,
                            'tag' => 1
                        ],
                        'stack' => 40
                    ],
                    'f' => [
                        'title' => 'Converters',
                        'type' => 'Items',
                        'block' => true,
                        'lot' => [
                            'link' => [
                                'title' => 'Automatically convert old internal URL into relative URL.',
                                'frozen' => true,
                                'value' => 1
                            ],
                            'p' => [
                                'active' => $ok = null !== State::get('x.p'),
                                'title' => 'Convert line-break sequence into paragraph sequence.',
                                'description' => $ok ? null : 'Missing automatic paragraph extension.',
                                'value' => 1
                            ],
                            'image' => [
                                'title' => 'Download images in posts and pages and convert its URL into local image URL.',
                                'value' => 1
                            ],
                            'h-t-m-l' => [
                                'title' => 'Convert XHTML tags to HTML5 tags.',
                                'description' => 'Convert &lt;br/&gt; to &lt;br&gt;, &lt;b&gt; to &lt;strong&gt;, etc.',
                                'value' => 1
                            ]
                        ],
                        'value' => [
                            'link' => 1,
                            'p' => $ok ? 1 : null,
                            'h-t-m-l' => 1
                        ],
                        'stack' => 50
                    ],
                    'safe' => [
                        'title' => 'Safe Mode',
                        'alt' => 'Store all blog data to a separate folder.',
                        'description' => 'You can move those files later, manually, after all importing process has done.',
                        'type' => 'Toggle',
                        'value' => true,
                        'stack' => 60
                    ],
                    'is' => [
                        'title' => '',
                        'type' => 'Items',
                        'lot' => [
                            'author' => [
                                'title' => 'I am responsible for the actions that I do and I declare that I am the original author of this blog.',
                                'value' => 1
                            ]
                        ],
                        'stack' => 70
                    ]
                ],
                'stack' => 10
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
