<?php

use BetaKiller\Env\AppEnvInterface;
use Geocoder\Provider\Chain\Chain;
use Geocoder\Provider\Geonames\Geonames;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Provider\Provider;
use Psr\Http\Client\ClientInterface;
use function DI\{factory};

return [
    'definitions' => [

        Provider::class => factory(static function (AppEnvInterface $env, ClientInterface $httpClient) {
            $providers = [];

            $googleApiKey  = $env->getEnvVariable('GOOGLE_CLOUD_API_KEY');
            $geoNamesLogin = $env->getEnvVariable('GEONAMES_LOGIN');

            if ($googleApiKey) {
                $providers[] = new GoogleMaps($httpClient, null, $googleApiKey);
            }

            if ($geoNamesLogin) {
                $providers[] = new Geonames($httpClient, $geoNamesLogin);
            }

            if (!$providers) {
                throw new InvalidArgumentException('Configure Geocoder providers via env variables');
            }

            return new Chain($providers);
        }),
    ],
];
