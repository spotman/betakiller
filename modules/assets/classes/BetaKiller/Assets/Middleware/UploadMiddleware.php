<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Middleware;

use BetaKiller\Assets\Exception\AssetsException;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Assets\Provider\HasPreviewProviderInterface;
use BetaKiller\Exception\ValidationException;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function count;

class UploadMiddleware extends AbstractAssetMiddleware
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

        /** @var \Psr\Http\Message\UploadedFileInterface[] $files */
        $files = $request->getUploadedFiles();

        // Restrict multiple files at once
        if (count($files) > 1) {
            throw new AssetsException('Only one file can be uploaded at once');
        }

        $this->detectProvider($request);

        $this->checkAction(AssetsProviderInterface::ACTION_UPLOAD);

        // Getting first uploaded file
        $file = array_shift($files);

        // Getting additional POST data
        $postData = ServerRequestHelper::getPost($request);

        // Uploading via provider
        $model = $this->provider->upload($file, $postData, $user);

        try {
            // Save model in database
            $this->provider->saveModel($model);
        } catch (ValidationException $e) {
            throw new AssetsException(':error', [':error' => $e->getFirstItem()->getMessage()]);
        }

        $data = [
            'id'          => $model->getID(),
            'originalUrl' => $this->provider->getOriginalUrl($model),
        ];

        if ($this->provider instanceof HasPreviewProviderInterface) {
            $previews = [];

            foreach ($this->provider->getAllowedPreviewSizes() as $previewSize) {
                $previews[$previewSize] = $this->provider->getPreviewUrl($model, $previewSize);
            }

            $data['previews'] = $previews;
        }

        return ResponseHelper::json($data);
    }
}
