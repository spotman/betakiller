<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Middleware;

use BetaKiller\Assets\Exception\AssetsException;
use BetaKiller\Assets\Exception\PreviewIsNotAvailableException;
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

        $previewContent = $this->provider->getCachedContent($model, $action, $size);

        if (!$previewContent) {
            try {
                $previewContent = $this->provider->makePreviewContent($model, $size);
            }
            /** @noinspection BadExceptionsProcessingInspection */
            catch (PreviewIsNotAvailableException $e) {
                return ResponseHelper::text('NOT AVAILABLE', 404);
            }

            // Cache preview to storage
            $this->provider->cacheContent($model, $previewContent, $action, $size);
        }

        // Deploy to cache if needed
        $this->deploy($model, $previewContent, $action, $size);

        // Send file content + headers
        $response = ResponseHelper::fileContent($previewContent, $model->getMime());

        return ResponseHelper::enableCaching($response, $model->getLastModifiedAt(), new \DateInterval('P1D'));
    }
}
