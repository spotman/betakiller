<?php
declare(strict_types=1);

return [
    // https://dev.maxmind.com/geoip/geoip2/geolite2/
    'maxmind' => [
        'downloadUrls' => [
            'countries' => [
                'bin' => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.tar.gz',
                'csv' => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country-CSV.zip',
            ],
            'cities'    => [
                'bin' => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.tar.gz',
                'csv' => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City-CSV.zip',
            ],
        ],
        // languages alias [appLanguageLocale:fileLanguageLocale,..]
        'languages'      => [
            'de-DE' => 'de',
            'en-GB' => 'en',
            'fr-FR' => 'fr',
            'es-ES' => 'es',
            'pt-PT' => 'pt-BR',
            'zh-CH' => 'zh-CN',
            'ja-JP' => 'ja',
            'ru-RU' => 'ru',
        ],
    ],
];
