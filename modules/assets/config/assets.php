<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\Assets\Provider\AbstractAssetsProvider;
use BetaKiller\Assets\Storage\LocalAssetsStorage;

$assetsLocalStorageBasePath = MultiSite::instance()->getSitePath().DIRECTORY_SEPARATOR.'assets';

return [

    'url_path' => '/assets',

    'deploy' => [
        'enabled'        => in_array(Kohana::$environment, [Kohana::STAGING, Kohana::PRODUCTION], true),
        'doc_root'       => MultiSite::instance()->docRoot(),
    ],

    AbstractAssetsProvider::CONFIG_STORAGES_KEY => [
        LocalAssetsStorage::CODENAME => [
            AbstractAssetsProvider::CONFIG_STORAGE_BASE_PATH_KEY => $assetsLocalStorageBasePath,
        ],
    ],

//    'models' => array(
//        'codename'  =>  array(
//            'url_key'  =>  'some-url-key',
//            'provider'  =>  'ProviderCodename',
//        )
//    )

];
