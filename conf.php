<?php
/**
 * PrivateBin Configuration File
 * 
 * This is a basic configuration for PrivateBin.
 * Adjust settings according to your needs.
 */

return [
    'main' => [
        'name' => 'PrivateBin',
        'discussion' => true,
        'opendiscussion' => false,
        'password' => true,
        'fileupload' => false,
        'burnafterreadingselected' => false,
        'defaultformatter' => 'plaintext',
        'syntaxhighlightingtheme' => 'sons-of-obsidian',
        'sizelimit' => 10485760,
        'template' => 'bootstrap',
        'info' => 'More information on the <a href="https://privatebin.info/">project page</a>.',
        'notice' => '',
        'languageselection' => false,
        'languagedefault' => 'en',
        'urlshortener' => '',
        'qrcode' => true,
        'icon' => 'identicon',
        'cspheader' => 'default-src \'none\'; base-uri \'self\'; form-action \'self\'; manifest-src \'self\'; connect-src * blob:; script-src \'self\' \'wasm-unsafe-eval\'; style-src \'self\'; font-src \'self\'; frame-ancestors \'none\'; frame-src blob:; img-src \'self\' data: blob:; media-src blob:; object-src blob:; sandbox allow-same-origin allow-scripts allow-forms allow-modals allow-downloads',
        'zerobincompatibility' => false,
        'httpwarning' => true,
        'compression' => 'zlib',
    ],
    'expire' => [
        'default' => '1week',
    ],
    'expire_options' => [
        '5min' => 300,
        '10min' => 600,
        '1hour' => 3600,
        '1day' => 86400,
        '1week' => 604800,
        '1month' => 2592000,
        '1year' => 31536000,
        'never' => 0,
    ],
    'formatter_options' => [
        'plaintext' => 'Plain Text',
        'syntaxhighlighting' => 'Source Code',
        'markdown' => 'Markdown',
    ],
    'traffic' => [
        'limit' => 10,
        'header' => 'X_FORWARDED_FOR',
        'creators' => [],
    ],
    'purge' => [
        'limit' => 300,
        'batchsize' => 10,
    ],
    'model' => [
        'class' => 'Filesystem',
    ],
    'model_options' => [
        'dir' => 'data',
    ],
];
