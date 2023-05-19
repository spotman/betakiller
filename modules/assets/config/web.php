<?php
declare(strict_types=1);

use BetaKiller\Assets\Middleware\DeleteMiddleware;
use BetaKiller\Assets\Middleware\DownloadMiddleware;
use BetaKiller\Assets\Middleware\OriginalMiddleware;
use BetaKiller\Assets\Middleware\PreviewMiddleware;
use BetaKiller\Assets\Middleware\UploadInfoMiddleware;
use BetaKiller\Assets\Middleware\UploadMiddleware;
use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Provider\HasPreviewProviderInterface;
use BetaKiller\Assets\StaticFilesDeployHandler;
use BetaKiller\Config\WebConfig;

// Assets
$extRegexp  = '[a-z]{2,}'; // (jpg|jpeg|gif|png)
$sizeRegexp = '[0-9]{0,4}'.AssetsModelImageInterface::SIZE_DELIMITER.'[0-9]{0,4}';

$itemPlace = '{item:.+}';
$sizePlace = '-{size:'.$sizeRegexp.'}';
$extPlace  = '.{ext:'.$extRegexp.'}';

$uploadAction   = AssetsProviderInterface::ACTION_UPLOAD;
$downloadAction = AssetsProviderInterface::ACTION_DOWNLOAD;
$originalAction = AssetsProviderInterface::ACTION_ORIGINAL;
$deleteAction   = AssetsProviderInterface::ACTION_DELETE;
$previewAction  = HasPreviewProviderInterface::ACTION_PREVIEW;

$uploadUrl = '/assets/{provider}/'.$uploadAction;

return [
    WebConfig::KEY_ROUTES => [
        WebConfig::KEY_GET => [
            /**
             * Get upload info and restrictions
             *
             * "assets/<provider>/upload"
             */
            $uploadUrl                                                               => UploadInfoMiddleware::class,

            /**
             * Static files legacy route first
             */
            '/assets/static/{file:.+}'                                               => StaticFilesDeployHandler::class,

            /**
             * Download original file via provider
             */
            '/assets/{provider}/'.$itemPlace.'/'.$downloadAction.$extPlace           => DownloadMiddleware::class,

            /**
             * Get original files via provider
             */
            '/assets/{provider}/'.$itemPlace.'/'.$originalAction.$extPlace           => OriginalMiddleware::class,

            /**
             * Preview files via provider
             */
            '/assets/{provider}/'.$itemPlace.'/'.$previewAction.$sizePlace.$extPlace => PreviewMiddleware::class,

            /**
             * Delete files via concrete provider
             */
            '/assets/{provider}/'.$itemPlace.'/'.$deleteAction.$extPlace             => DeleteMiddleware::class,
        ],

        WebConfig::KEY_POST => [
            /**
             * Upload file via concrete provider
             *
             * "assets/<provider>/upload"
             */
            $uploadUrl => UploadMiddleware::class,
        ],
    ],

];
