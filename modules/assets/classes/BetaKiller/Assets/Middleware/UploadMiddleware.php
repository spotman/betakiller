<?php

declare(strict_types=1);

namespace BetaKiller\Assets\Middleware;

use BetaKiller\Assets\AssetsDeploymentService;
use BetaKiller\Assets\AssetsHandlerFactory;
use BetaKiller\Assets\AssetsProviderFactory;
use BetaKiller\Assets\Exception\AssetsException;
use BetaKiller\Assets\Exception\AssetsProviderException;
use BetaKiller\Assets\Exception\AssetsUploadException;
use BetaKiller\Assets\Exception\CantWriteUploadException;
use BetaKiller\Assets\Exception\ExtensionHaltedUploadException;
use BetaKiller\Assets\Exception\FormSizeUploadException;
use BetaKiller\Assets\Exception\IniSizeUploadException;
use BetaKiller\Assets\Exception\NoFileUploadException;
use BetaKiller\Assets\Exception\NoTmpDirUploadException;
use BetaKiller\Assets\Exception\PartialUploadException;
use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Config\AssetsConfig;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Exception\ValidationException;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

use Psr\Log\LoggerInterface;

use function count;

class UploadMiddleware extends AbstractAssetMiddleware
{
    public function __construct(
        private AssetsConfig $config,
        private AssetsHandlerFactory $handlerFactory,
        AssetsProviderFactory $providerFactory,
        AssetsDeploymentService $deploymentService,
        LoggerInterface $logger
    ) {
        parent::__construct($providerFactory, $deploymentService, $logger);
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
        $user = ServerRequestHelper::getUser($request);

        /** @var \Psr\Http\Message\UploadedFileInterface[] $files */
        $files = $request->getUploadedFiles();

        // Restrict multiple files at once
        if (!$files || count($files) !== 1) {
            throw new BadRequestHttpException();
        }

        $this->detectProvider($request);

        $this->checkAction(AssetsProviderInterface::ACTION_UPLOAD, $user, null);

        // Getting first uploaded file
        $file = array_shift($files);

        // Getting additional POST data
        $postData = ServerRequestHelper::getPost($request);

        // Uploading via provider
        $model = $this->upload($this->provider, $file, $postData, $user);

        try {
            // Save model in database
            $this->provider->saveModel($model);
        } catch (ValidationException $e) {
            throw new AssetsException(':error', [':error' => $e->getFirstItem()->getMessage()]);
        }

        return ResponseHelper::successJson($this->provider->getInfo($model));
    }

    private function upload(
        AssetsProviderInterface $provider,
        UploadedFileInterface $file,
        array $postData,
        UserInterface $user
    ): AssetsModelInterface {
        // Check permissions
        if (!$provider->isUploadAllowed($user)) {
            throw new AssetsProviderException('Upload is not allowed');
        }

        // Security checks
        $this->checkUploadedFile($file);

        // Move uploaded file to temp location (and proceed underlying checks)
        $fullPath = tempnam(sys_get_temp_dir(), 'assets-upload');
        $file->moveTo($fullPath);

        // Store temp file
        $name  = strip_tags($file->getClientFilename());
        $model = $provider->store($fullPath, $name, $user);

        $this->postUploadProcessing($model, $postData, $user);

        // Cleanup
        unlink($fullPath);

        return $model;
    }

    private function checkUploadedFile(UploadedFileInterface $file): void
    {
        if (!$file->getSize()) {
            throw new NoFileUploadException;
        }

        // TODO i18n for exceptions
        switch ($file->getError()) {
            case UPLOAD_ERR_OK:
                return;

            case UPLOAD_ERR_CANT_WRITE:
                throw new CantWriteUploadException;

            case UPLOAD_ERR_NO_TMP_DIR:
                throw new NoTmpDirUploadException;

            case UPLOAD_ERR_EXTENSION:
                throw new ExtensionHaltedUploadException;

            case UPLOAD_ERR_FORM_SIZE:
                throw new FormSizeUploadException;

            case UPLOAD_ERR_INI_SIZE:
                throw new IniSizeUploadException;

            case UPLOAD_ERR_NO_FILE:
                throw new NoFileUploadException;

            case UPLOAD_ERR_PARTIAL:
                throw new PartialUploadException;

            default:
                throw new AssetsUploadException;
        }
    }

    /**
     * After upload processing
     *
     * @param AssetsModelInterface            $model
     * @param array                           $postData
     * @param \BetaKiller\Model\UserInterface $user
     */
    protected function postUploadProcessing(AssetsModelInterface $model, array $postData, UserInterface $user): void
    {
        $handlersNames = $this->config->getProviderPostUploadHandlers($this->provider);

        if (!$handlersNames) {
            return;
        }

        foreach ($handlersNames as $handlerName) {
            $handler = $this->handlerFactory->create($handlerName);

            $handler->update($this->provider, $model, $postData, $user);
        }
    }
}
