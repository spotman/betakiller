<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Middleware;

use BetaKiller\Assets\AssetsDeploymentService;
use BetaKiller\Assets\AssetsProviderFactory;
use BetaKiller\Assets\Exception\AssetsException;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Provider\ImageAssetsProviderInterface;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Exception\FoundHttpException;
use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Helper\LoggerHelperTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractAssetMiddleware implements RequestHandlerInterface
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Assets\AssetsProviderFactory
     */
    private $providerFactory;

    /**
     * @var \BetaKiller\Assets\AssetsDeploymentService
     */
    private $deploymentService;

    /**
     * @var \BetaKiller\Assets\Provider\AssetsProviderInterface
     */
    protected $provider;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * AbstractAssetMiddleware constructor.
     *
     * @param \BetaKiller\Assets\AssetsProviderFactory   $providerFactory
     * @param \BetaKiller\Assets\AssetsDeploymentService $deploymentService
     * @param \Psr\Log\LoggerInterface                   $logger
     */
    public function __construct(
        AssetsProviderFactory $providerFactory,
        AssetsDeploymentService $deploymentService,
        LoggerInterface $logger
    ) {
        $this->providerFactory   = $providerFactory;
        $this->deploymentService = $deploymentService;
        $this->logger            = $logger;
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param string                                        $content
     * @param string                                        $action
     * @param null|string                                   $suffix
     *
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     */
    protected function deploy(AssetsModelInterface $model, string $content, string $action, ?string $suffix = null): void
    {
        $this->deploymentService->deploy($this->provider, $model, $content, $action, $suffix);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function detectProvider(ServerRequestInterface $request): void
    {
        $requestKey = $request->getAttribute('provider');

        if (!$requestKey) {
            throw new AssetsException('You must specify provider codename');
        }

        $this->provider = $this->providerFactory->createFromUrlKey($requestKey);
        $providerKey    = $this->provider->getUrlKey();

        $this->checkAction($request);

        if ($requestKey !== $providerKey) {
            // Redirect to canonical url
            $model = $this->fromItemDeployUrl($request);

            $this->redirectToCanonicalUrl($model, $request);
        }
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \BetaKiller\Assets\Model\AssetsModelInterface|\BetaKiller\Assets\Model\AssetsModelImageInterface
     * @throws \BetaKiller\Assets\Exception\AssetsException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     */
    protected function fromItemDeployUrl(ServerRequestInterface $request)
    {
        $url = $request->getAttribute('item_url');

        if (!$url) {
            throw new AssetsException('You must specify item url');
        }

        try {
            // Find asset model by url
            return $this->provider->getModelByPublicUrl($url);
        } catch (AssetsException $e) {
            $this->logException($this->logger, $e);
            // File not found
            throw new NotFoundHttpException;
        }
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param \Psr\Http\Message\ServerRequestInterface      $request
     *
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     */
    protected function checkExtension(AssetsModelInterface $model, ServerRequestInterface $request): void
    {
        $requestExt = $request->getAttribute('ext');
        $modelExt   = $this->provider->getModelExtension($model);

        if (!$requestExt || $requestExt !== $modelExt) {
            $this->redirectToCanonicalUrl($model, $request);
        }
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    protected function checkAction(ServerRequestInterface $request): void
    {
        $action = $request->getAttribute('action');

        if (!$this->provider->hasAction($action)) {
            throw new NotImplementedHttpException('Action :name is not allowed for provider :codename', [
                ':name'     => $action,
                ':codename' => $this->provider->getCodename(),
            ]);
        }
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @param \Psr\Http\Message\ServerRequestInterface      $request
     *
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     */
    protected function redirectToCanonicalUrl(AssetsModelInterface $model, ServerRequestInterface $request): void
    {
        $url = $this->getCanonicalUrl($model, $request);

        throw new FoundHttpException($url);
    }

    private function getAction(ServerRequestInterface $request): string
    {
        return $request->getAttribute('action');
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param \Psr\Http\Message\ServerRequestInterface      $request
     *
     * @return string
     * @throws \BetaKiller\Assets\Exception\AssetsProviderException
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     */
    private function getCanonicalUrl(AssetsModelInterface $model, ServerRequestInterface $request): string
    {
        $action = $this->getAction($request);

        switch ($action) {
            case AssetsProviderInterface::ACTION_ORIGINAL:
                return $this->provider->getOriginalUrl($model);

            case ImageAssetsProviderInterface::ACTION_PREVIEW:
                if (!($this->provider instanceof ImageAssetsProviderInterface)) {
                    throw new BadRequestHttpException('Action :name may be used on images only', [
                        ':name' => $action,
                    ]);
                }

                return $this->provider->getPreviewUrl($model, $this->getSizeParam($request));

            case AssetsProviderInterface::ACTION_DELETE:
                return $this->provider->getDeleteUrl($model);

            default:
                throw new NotFoundHttpException('Unknown action :value', [':value' => $action]);
        }
    }

    protected function getSizeParam(ServerRequestInterface $request): ?string
    {
        return $request->getAttribute('size');
    }
}
