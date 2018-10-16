<?php
declare(strict_types=1);

use BetaKiller\Assets\ContentTypes;
use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StaticFilesDeployHandler implements RequestHandlerInterface
{
    /**
     * @var mixed[]
     */
    private $config;

    /**
     * @var \BetaKiller\Assets\ContentTypes
     */
    private $types;

    /**
     * StaticFilesDeployHandler constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $configProvider
     * @param \BetaKiller\Assets\ContentTypes            $types
     */
    public function __construct(ConfigProviderInterface $configProvider, ContentTypes $types)
    {
        $this->config = (array)$configProvider->load(['staticfiles']);
        $this->types  = $types;
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
        $file = $request->getAttribute('file');

        $orig = StaticFile::findOriginal($file);

        if (!$orig) {
            // Return a 404 status
            throw new NotFoundHttpException('File [:file] not found', [':file' => $file]);
        }

        // Получаем время модификации оригинала
        $isEnabled = ($this->config['enabled'] === true);

        // Сохраняем в кеш
        if ($isEnabled) {
            // Производим deploy статического файла,
            // В следующий раз его будет отдавать сразу nginx без запуска PHP
            $deploy = $this->deploy($file);

            symlink($orig, $deploy);
        }

//            if (!$is_enabled) {
//                $this->response->headers('Pragma', 'no-cache');
//                $this->response->headers('Expires', gmdate("D, d M Y H:i:s \G\M\T", time() - 3600));
//            }

        $info = pathinfo($file);
        $mime = $this->types->getExtensionMimeType($info['extension']);

        return ResponseHelper::file($orig, $mime);
    }

    /**
     * @param $file
     *
     * @return string
     * @throws \RuntimeException
     */
    private function deploy($file)
    {
        $info   = pathinfo($file);
        $dir    = ($info['dirname'] !== '.') ? $info['dirname'].'/' : '';
        $deploy = rtrim($this->config['path'], '/')
            .'/'
            .ltrim($this->config['url'], '/').$dir
            .$info['filename'].'.'
            .$info['extension'];

        $dir = dirname($deploy);

        if (!file_exists($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        return $deploy;
    }
}
