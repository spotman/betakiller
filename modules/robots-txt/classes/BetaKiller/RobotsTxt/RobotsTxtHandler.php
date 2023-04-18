<?php
declare(strict_types=1);

namespace BetaKiller\RobotsTxt;

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RobotsTxtHandler implements RequestHandlerInterface
{
    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private $appEnv;

    /**
     * RobotsTxtHandler constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv)
    {
        $this->appEnv = $appEnv;
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
        $appPath = $this->appEnv->getAppRootPath();
        $mode    = $this->appEnv->getModeName();

        $mime     = 'text/plain';
        $fileName = $appPath.\DIRECTORY_SEPARATOR.$mode.'.robots.txt';

        // Serve env-related file if exists
        if (\file_exists($fileName)) {
            return ResponseHelper::file($fileName, $mime);
        }

        // Serve fallback content (deny everything)
        return ResponseHelper::fileContent($this->getDenyContent(), $mime);
    }

    private function getDenyContent(): string
    {
        return <<<EOD
User-agent: *
Disallow: /
EOD;
    }
}
