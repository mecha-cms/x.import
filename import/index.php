<?php

if (!is_dir($d = LOT . DS . '.import')) {
    mkdir($d, 0775, true);
}

// Route is public as long as it has proper token value
require __DIR__ . DS . 'engine' . DS . 'r' . DS . 'route.php';
