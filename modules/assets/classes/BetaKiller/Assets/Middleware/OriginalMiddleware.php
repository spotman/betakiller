<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Middleware;

use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OriginalMiddleware extends AbstractAssetMiddleware
{
    /**
     * Handle the request and return a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->detectProvider($request);

        $model = $this->fromItemDeployUrl($request);

        $this->checkExtension($model, $request);

        // Get file content
        $content = $this->provider->getContent($model);

        $this->deploy($model, $content, AssetsProviderInterface::ACTION_ORIGINAL);

        // Send file content + headers
        $response = ResponseHelper::fileContent($content, $model->getMime(), $model->getOriginalName());

        // Send last modified date
        return ResponseHelper::setLastModified($response, $model->getLastModifiedAt());
    }
}
