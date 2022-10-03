<?php

return [
    'admin-url' => env('ADMIN_URL', 'admin-panel'),  
    'admin-domain' => env('ADMIN_DOMAIN',''),
    'admin-ip' => env('ADMIN_IP',''),
    'recaptcha' => [
        'key' => env('RECAPTCHA_KEY',''),
        'secret' => env('RECAPTCHA_SECRET',''),
    ],
    'documentation-file' => storage_path('documentation.pdf'),
    'go-to-web' => '[site]',
    'after-login' => null,
    'excl-copy-trans' => ['meta_title','meta_keywords','meta_Description','permalink'], // for copy translations
];