<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Middleware;

use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeleteMiddleware extends AbstractAssetMiddleware
{
    /**
     * Common action for uploading files through provider
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request): ResponseInterface
    {
        $user = ServerRequestHelper::getUser($request);

        $this->detectProvider($request);

        // Get file model by hash value
        $model = $this->fromItemDeployUrl($request);

        $this->checkProviderKey($model, $request);

        $this->checkAction(AssetsProviderInterface::ACTION_DELETE, $user, $model);

        // Delete file through provider
        $this->provider->delete($model, $user);

        return ResponseHelper::successJson();
    }
}
