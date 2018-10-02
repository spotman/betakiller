<?php

use BetaKiller\Assets\AssetsException;
use BetaKiller\Assets\AssetsProviderFactory;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Provider\HasPreviewProviderInterface;
use BetaKiller\Assets\Provider\ImageAssetsProviderInterface;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Exception\FoundHttpException;
use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Exception\ValidationException;

class Controller_Assets extends Controller
{
    /**
     * @Inject
     * @var AssetsProviderFactory
     */
    private $providerFactory;

    /**
     * @Inject
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @Inject
     * @var \BetaKiller\Assets\AssetsDeploymentService
     */
    private $deploymentService;

    /**
     * @var AssetsProviderInterface
     */
    private $provider;

    /**
     * Common action for uploading files through provider
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Assets\AssetsException
     */
    public function action_upload(): void
    {
        // This method responds via JSON (all exceptions will be caught automatically)
        $this->content_type_json();

        // Restrict multiple files at once
        if (count($_FILES) > 1) {
            throw new AssetsException('Only one file can be uploaded at once');
        }

        $this->detectProvider();

        // Getting first uploaded file
        $file = array_shift($_FILES);

        // Getting additional POST data
        $postData = $this->request->post();

        // Uploading via provider
        $model = $this->provider->upload($file, $postData, $this->user);

        try {
            // Save model in database
            $this->provider->saveModel($model);
        } catch (ValidationException $e) {
            throw new AssetsException(':error', [':error' => $e->getFirstItem()->getMessage()]);
        }

        // Returns
        $this->send_json(self::JSON_SUCCESS, $model->toJson());
    }

    /**
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \HTTP_Exception_500
     */
    public function action_original(): void
    {
        $this->detectProvider();

        $model = $this->fromItemDeployUrl();

        $this->checkExtension($model);

        // Get file content
        $content = $this->provider->getContent($model);

        $this->deploy($model, $content, AssetsProviderInterface::ACTION_ORIGINAL);

        // Send last modified date
        $this->response->last_modified($model->getLastModifiedAt());

        // Send file content + headers
        $this->send_file($content, $model->getMime());
    }

    /**
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \HTTP_Exception_500
     */
    public function action_download(): void
    {
        $this->detectProvider();

        $model = $this->fromItemDeployUrl();

        $this->checkExtension($model);

        // Get file content
        $content = $this->provider->getContent($model);

        // Send last modified date
        $this->response->last_modified($model->getLastModifiedAt());

        // Send file content + headers
        $this->send_file($content, $model->getMime(), $model->getOriginalName(), true);
    }

    /**
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \HTTP_Exception_500
     */
    public function action_preview(): void
    {
        $this->detectProvider();

        if (!($this->provider instanceof HasPreviewProviderInterface)) {
            throw new AssetsException('Preview can be served only by instances of :must', [
                ':must' => HasPreviewProviderInterface::class,
            ]);
        }

        $size   = $this->getSizeParam();
        $model  = $this->fromItemDeployUrl();
        $action = HasPreviewProviderInterface::ACTION_PREVIEW;

        $this->checkExtension($model);

        // Redirect to default size
        if (!$size) {
            $this->redirectToCanonicalUrl($model);
        }

        $previewContent = $this->provider->makePreviewContent($model, $size);

        // Cache preview to storage
        $this->provider->cacheContent($model, $previewContent, $action, $size);

        // Deploy to cache if needed
        $this->deploy($model, $previewContent, $action, $size);

        // Send last modified date
        $this->response->last_modified($model->getLastModifiedAt());

        // Send file content + headers
        $this->send_file($previewContent, $model->getMime());
    }

    private function getSizeParam(): ?string
    {
        return $this->param('size');
    }

    /**
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function action_delete(): void
    {
        // This method responds via JSON (all exceptions will be caught automatically)
        $this->content_type_json();

        $this->detectProvider();

        // Get file model by hash value
        $model = $this->fromItemDeployUrl();

        // Delete file through provider
        $this->provider->delete($model, $this->user);

        $this->send_json(self::JSON_SUCCESS);
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     * @param string                                        $content
     * @param string                                        $action
     * @param null|string                                   $suffix
     *
     * @throws \BetaKiller\Assets\AssetsProviderException
     */
    private function deploy(AssetsModelInterface $model, string $content, string $action, ?string $suffix = null): void
    {
        $this->deploymentService->deploy($this->provider, $model, $content, $action, $suffix);
    }

    /**
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Assets\AssetsException
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Assets\AssetsStorageException
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function detectProvider(): void
    {
        $requestKey = $this->param('provider');

        if (!$requestKey) {
            throw new AssetsException('You must specify provider codename');
        }

        $this->provider = $this->providerFactory->createFromUrlKey($requestKey);
        $providerKey    = $this->provider->getUrlKey();

        $this->checkAction();

        if ($requestKey !== $providerKey) {
            // Redirect to canonical url
            $model = $this->fromItemDeployUrl();

            $this->redirectToCanonicalUrl($model);
        }
    }

    /**
     * @return \BetaKiller\Assets\Model\AssetsModelInterface|\BetaKiller\Assets\Model\AssetsModelImageInterface
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Assets\AssetsException
     */
    private function fromItemDeployUrl()
    {
        $url = $this->param('item_url');

        if (!$url) {
            throw new AssetsException('You must specify item url');
        }

        try {
            // Find asset model by url
            return $this->provider->getModelByPublicUrl($url);
        } /** @noinspection BadExceptionsProcessingInspection */ catch (AssetsException $e) {
            // File not found
            throw new NotFoundHttpException;
        }
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     */
    private function checkExtension(AssetsModelInterface $model): void
    {
        $requestExt = $this->param('ext');
        $modelExt   = $this->provider->getModelExtension($model);

        if (!$requestExt || $requestExt !== $modelExt) {
            $this->redirectToCanonicalUrl($model);
        }
    }

    /**
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    private function checkAction(): void
    {
        $action = $this->action();

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
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     */
    private function redirectToCanonicalUrl(AssetsModelInterface $model): void
    {
        $url = $this->getCanonicalUrl($model);

        throw new FoundHttpException($url);
    }

    /**
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Exception\BadRequestHttpException
     * @throws \BetaKiller\Assets\AssetsProviderException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     */
    private function getCanonicalUrl(AssetsModelInterface $model): string
    {
        $action = $this->action();

        switch ($action) {
            case AssetsProviderInterface::ACTION_ORIGINAL:
                return $this->provider->getOriginalUrl($model);

            case ImageAssetsProviderInterface::ACTION_PREVIEW:
                if (!($this->provider instanceof ImageAssetsProviderInterface)) {
                    throw new BadRequestHttpException('Action :name may be used on images only', [':name' => $action]);
                }

                return $this->provider->getPreviewUrl($model, $this->getSizeParam());

            case AssetsProviderInterface::ACTION_DELETE:
                return $this->provider->getDeleteUrl($model);

            default:
                throw new NotFoundHttpException('Unknown action :value', [':value' => $action]);
        }
    }
}
