<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Middleware;

use BetaKiller\Assets\AssetsDeploymentService;
use BetaKiller\Assets\AssetsProviderFactory;
use BetaKiller\Assets\ContentTypes;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Provider\ImageAssetsProviderInterface;
use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class UploadInfoMiddleware extends AbstractAssetMiddleware
{
    /**
     * @var \BetaKiller\Assets\ContentTypes
     */
    private $types;

    /**
     * AbstractAssetMiddleware constructor.
     *
     * @param \BetaKiller\Assets\ContentTypes            $types
     * @param \BetaKiller\Assets\AssetsProviderFactory   $providerFactory
     * @param \BetaKiller\Assets\AssetsDeploymentService $deploymentService
     * @param \Psr\Log\LoggerInterface                   $logger
     */
    public function __construct(
        ContentTypes $types,
        AssetsProviderFactory $providerFactory,
        AssetsDeploymentService $deploymentService,
        LoggerInterface $logger
    ) {
        parent::__construct($providerFactory, $deploymentService, $logger);

        $this->types = $types;
    }

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
            'types' => $mimeTypes === true ? ['*'] : $mimeTypes,
            'extensions' => $mimeTypes === true ? ['*'] : $this->types->getTypesExtensions($mimeTypes),
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
