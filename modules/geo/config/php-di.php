<?php

use Http\Client\HttpClient;
use function DI\{factory};

return [
    'definitions' => [

        \Geocoder\Provider\Provider::class => factory(function (HttpClient $httpClient) {
            $googleApiKey = getenv('GOOGLE_CLOUD_API_KEY');

            if (!$googleApiKey) {
                throw new InvalidArgumentException('Missing GOOGLE_CLOUD_API_KEY env variable');
            }

            return new \Geocoder\Provider\Chain\Chain([
                new \Geocoder\Provider\GoogleMaps\GoogleMaps($httpClient, $googleApiKey),
            ]);
        }),
    ],
];
