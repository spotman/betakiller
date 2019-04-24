<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Middleware;

use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Provider\ImageAssetsProviderInterface;
use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UploadInfoMiddleware extends AbstractAssetMiddleware
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
        $this->detectProvider($request);

        $this->checkAction(AssetsProviderInterface::ACTION_UPLOAD);

        $user = ServerRequestHelper::getUser($request);

        if (!$this->provider->isUploadAllowed($user)) {
            throw new AccessDeniedException('Fetching upload info is not allowed');
        }

        $mimeTypes = $this->provider->getAllowedMimeTypes();
        $maxSize   = $this->provider->getUploadMaxSize();

        $data = [
            'mime' => $mimeTypes === true ? ['*'] : $mimeTypes,
            'size' => $maxSize,
        ];

        if ($this->provider instanceof ImageAssetsProviderInterface) {
            $data['width']  = $this->provider->getUploadMaxWidth();
            $data['height'] = $this->provider->getUploadMaxHeight();
        }

//        if ($this->provider instanceof HasPreviewProviderInterface) {
//            $data['preview'] = $this->provider->getAllowedPreviewSizes();
//        }

        return ResponseHelper::successJson($data);
    }
}
