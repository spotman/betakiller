<?php

declare(strict_types=1);

namespace BetaKiller\Assets;

use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

readonly class StaticFilesDeployHandler implements RequestHandlerInterface
{
    /**
     * StaticFilesDeployHandler constructor.
     *
     * @param \BetaKiller\Assets\StaticAssetsFactory $assetsFactory
     * @param \BetaKiller\Assets\ContentTypes        $types
     */
    public function __construct(private StaticAssetsFactory $assetsFactory, private ContentTypes $types)
    {
    }

    /**
     * Handle the request and return a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Assets\Exception\AssetsException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $assets = $this->assetsFactory->create();

        $file = $request->getAttribute('file');

        // Replace URL separators with FS ones
        $path = str_replace('/', DIRECTORY_SEPARATOR, $file);

        $orig = $assets->findFile($path);

        if (!$orig) {
            // Return a 404 status
            throw new NotFoundHttpException('File [:file] not found', [':file' => $file]);
        }

        // Deploy static file via symlink
        // On next request file will be served directly by nginx
        $deployPath = $assets->getDeployPath($path);

        // Create target directory if not exists
        $deployDir = dirname($deployPath);

        if (!file_exists($deployDir) && !mkdir($deployDir, 0777, true) && !is_dir($deployDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $deployDir));
        }

        if (!file_exists($deployPath)) {
            symlink($orig, $deployPath);
        }

        $ext  = pathinfo($file, PATHINFO_EXTENSION);
        $mime = $this->types->getExtensionMimeType($ext);

        return ResponseHelper::file($orig, $mime);
    }
}
