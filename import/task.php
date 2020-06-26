<?php

// My personal blog post filter
Hook::set('blogger.fix', function($content) {
    if (false !== strpos($content, '<pre ')) {
        $content = preg_replace_callback('/<pre(\s[^>]*)?>([\s\S]*?)<\/pre>/', function($m) {
            $attr = preg_replace('/\s(data-title=".*?"|class="numbered")/', "", $m[1] ?? "");
            $content = str_replace([
                '<code class="javascript">'
            ], [
                '<code class="js">'
            ], $m[2]);
            return '<pre' . $attr . '>' . $content . '</pre>';
        }, $content);
    }
    if (false !== strpos($content, '</figure>')) {
        $content = preg_replace('/<figure(\s[^>]*)?>/', '<figure>', $content);
    }
    if (false !== strpos($content, '<div class="separator')) {
        $content = preg_replace('/<div class="(.*?)?separator(.*?)?">([\s\S]*?)<\/div>/', '<figure>$1</figure>', $content);
    }
    return $content;
});
