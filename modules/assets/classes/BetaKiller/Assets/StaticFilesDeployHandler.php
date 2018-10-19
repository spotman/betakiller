<?php
declare(strict_types=1);

namespace BetaKiller\Assets;

use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StaticFilesDeployHandler implements RequestHandlerInterface
{
    /**
     * @var \BetaKiller\Assets\ContentTypes
     */
    private $types;

    /**
     * @var \BetaKiller\Assets\StaticAssetsFactory
     */
    private $assetsFactory;

    /**
     * StaticFilesDeployHandler constructor.
     *
     * @param \BetaKiller\Assets\StaticAssetsFactory $assetsFactory
     * @param \BetaKiller\Assets\ContentTypes        $types
     */
    public function __construct(StaticAssetsFactory $assetsFactory, ContentTypes $types)
    {
        $this->assetsFactory = $assetsFactory;
        $this->types         = $types;
    }

    /**
     * Handle the request and return a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $assets = $this->assetsFactory->create();

        $file = $request->getAttribute('file');

        $orig = $assets->findFile($file);

        if (!$orig) {
            // Return a 404 status
            throw new NotFoundHttpException('File [:file] not found', [':file' => $file]);
        }

        // Производим deploy статического файла,
        // В следующий раз его будет отдавать сразу nginx без запуска PHP
        $deployPath = $assets->getDeployPath($file);

        // Create target directory if not exists
        $deployDir = \dirname($deployPath);

        if (!file_exists($deployDir) && !mkdir($deployDir, 0777, true) && !is_dir($deployDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $deployDir));
        }

        symlink($orig, $deployPath);

        $info = pathinfo($file);
        $mime = $this->types->getExtensionMimeType($info['extension']);

        return ResponseHelper::file($orig, $mime);
    }
}
