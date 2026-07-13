<?php
declare(strict_types=1);

namespace BetaKiller\AppLinks;

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class AppLinksWellKnownHandler implements RequestHandlerInterface
{
    public function __construct(private AppEnvInterface $appEnv)
    {
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
        $fileName = $request->getAttribute('file');
        $appPath = $this->appEnv->getAppRootPath();
        $mode    = $this->appEnv->getModeName();

        $filePath = $appPath.\DIRECTORY_SEPARATOR.$mode.'.'.$fileName;

        // Serve fallback content
        if (!\file_exists($filePath)) {
            return ResponseHelper::text('Not found', 404);
        }

        // Serve env-related file if exists
        return ResponseHelper::file($filePath);
    }
}
