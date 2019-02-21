<?php

// Allow using custom domains with ngrok
$domain = getenv('HTTP_HOST');
$url = $domain ? 'https://'.$domain : getenv('APP_URL');

putenv('APP_URL='.$url);

return [
    'url' => [
        'base' =>  $url,
    ],
];
