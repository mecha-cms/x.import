<?php

$z = defined('DEBUG') && DEBUG ? '.min.' : '.';
Asset::set(__DIR__ . DS . '..' . DS . '..' . DS . 'lot' . DS . 'asset' . DS . 'css' . DS . 'import' . $z . 'css', 20.1);
Asset::set(__DIR__ . DS . '..' . DS . '..' . DS . 'lot' . DS . 'asset' . DS . 'js' . DS . 'import' . $z . 'js', 20.1);
