<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Middleware;

use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DownloadMiddleware extends AbstractAssetMiddleware
{
    /**
     * Handle the request and return a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request): ResponseInterface
    {
        $this->detectProvider($request);

        $user = ServerRequestHelper::getUser($request);

        $model = $this->fromItemDeployUrl($request);

        $this->checkProviderKey($model, $request);

        $this->checkAction(AssetsProviderInterface::ACTION_DOWNLOAD, $user, $model);

        $this->checkExtension($model, $request);

        // Get file content
        $content = $this->provider->getContent($model);

        // Send file content + headers
        $response = ResponseHelper::fileContent($content, $model->getMime(), $model->getOriginalName());

        // Send last modified date
        return ResponseHelper::enableCaching($response, $model->getLastModifiedAt(), new \DateInterval('P1M'));
    }
}
