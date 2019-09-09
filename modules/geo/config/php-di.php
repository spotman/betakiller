<?php

use Geocoder\Provider\Chain\Chain;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Provider\Provider;
use Geocoder\Provider\Yandex\Yandex;
use Http\Client\HttpClient;
use function DI\{factory};

return [
    'definitions' => [

        Provider::class => factory(static function (HttpClient $httpClient) {
            $googleApiKey = getenv('GOOGLE_CLOUD_API_KEY');

            if (!$googleApiKey) {
                throw new InvalidArgumentException('Missing GOOGLE_CLOUD_API_KEY env variable');
            }

            return new Chain([
                new GoogleMaps($httpClient, null, $googleApiKey),
//                new Yandex($httpClient),
            ]);
        }),
    ],
];
