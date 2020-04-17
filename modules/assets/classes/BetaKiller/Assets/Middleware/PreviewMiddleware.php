<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Middleware;

use BetaKiller\Assets\Exception\AssetsException;
use BetaKiller\Assets\Provider\HasPreviewProviderInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PreviewMiddleware extends AbstractAssetMiddleware
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

        if (!($this->provider instanceof HasPreviewProviderInterface)) {
            throw new AssetsException('Preview can be served only by instances of :must', [
                ':must' => HasPreviewProviderInterface::class,
            ]);
        }

        $user = ServerRequestHelper::getUser($request);

        $size   = $this->getSizeParam($request);
        $model  = $this->fromItemDeployUrl($request);
        $action = HasPreviewProviderInterface::ACTION_PREVIEW;

        $this->checkProviderKey($model, $request);

        $this->checkAction($action, $user, $model);

        $this->checkExtension($model, $request);

        // Redirect to default size
        if (!$size) {
            $this->redirectToCanonicalUrl($model, $request);
        }

        $previewContent = $this->provider->makePreviewContent($model, $size);

        // Cache preview to storage
        $this->provider->cacheContent($model, $previewContent, $action, $size);

        // Deploy to cache if needed
        $this->deploy($model, $previewContent, $action, $size);

        // Send file content + headers
        $response = ResponseHelper::fileContent($previewContent, $model->getMime());

        // Send last modified date
        return ResponseHelper::setLastModified($response, $model->getLastModifiedAt());
    }
}
