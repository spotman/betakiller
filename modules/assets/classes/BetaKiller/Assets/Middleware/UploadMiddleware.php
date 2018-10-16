<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Middleware;

use BetaKiller\Assets\Exception\AssetsException;
use BetaKiller\Exception\ValidationException;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UploadMiddleware extends AbstractAssetMiddleware
{
    /**
     * Common action for uploading files through provider
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = ServerRequestHelper::getUser($request);

        /** @var \Psr\Http\Message\UploadedFileInterface[] $files */
        $files = $request->getUploadedFiles();

        // Restrict multiple files at once
        if (\count($files) > 1) {
            throw new AssetsException('Only one file can be uploaded at once');
        }

        $this->detectProvider($request);

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

        return ResponseHelper::successJson($model->toJson());
    }
}
