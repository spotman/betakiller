<?php
declare(strict_types=1);

return [
    // https://dev.maxmind.com/geoip/geoip2/geolite2/
    'maxmind' => [
        'downloadUrls' => [
            'countries' => [
                'csv'    => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country-CSV.zip',
                'binary' => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.tar.gz',
            ],
            'cities' => [
                'csv'    => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City-CSV.zip',
                'binary' => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.tar.gz',
            ],
        ],
    ],
];
