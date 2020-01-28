<?php

Hook::set('get', function() {
    Asset::set(__DIR__ . DS . '..' . DS . '..' . DS . 'lot' . DS . 'asset' . DS . 'css' . DS . 'import.css');
    Asset::set(__DIR__ . DS . '..' . DS . '..' . DS . 'lot' . DS . 'asset' . DS . 'js' . DS . 'import.js');
}, 20.1);
