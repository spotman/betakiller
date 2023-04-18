<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Env\AppEnvInterface;
use HansOtt\PSR7Cookies\SetCookie;
use Psr\Http\Message\ResponseInterface;

class CookieHelper
{
    /**
     * @var string
     */
    private string $path = '/';

    /**
     * @var bool
     */
    private bool $secureOnly = false;

    /**
     * @var string
     */
    private string $sameSite = 'lax';

    /**
     * Session ID cookie is used for WAMP auth
     *
     * @var bool
     */
    private bool $httpOnly = false;

    /**
     * CookieHelper constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     * @param \BetaKiller\Env\AppEnvInterface       $appEnv
     *
     * @throws \HansOtt\PSR7Cookies\InvalidArgumentException
     */
    public function __construct(AppConfigInterface $appConfig, AppEnvInterface $appEnv)
    {
        if ($appConfig->isSecure() && !$appEnv->isInternalWebServer()) {
            $this->secureOnly = true;
        }
    }

    public function set(
        ResponseInterface $response,
        string            $name,
        string            $value,
        \DateInterval     $expiresIn
    ): ResponseInterface {
        $dt        = new \DateTimeImmutable();
        $expiresAt = $dt->setTimezone(DateTimeHelper::getUtcTimezone())->add($expiresIn)->getTimestamp();

        $cookie = new SetCookie(
            $name,
            $value,
            $expiresAt,
            $this->path,
            '', // No domain defined => lock cookies to current domain only
            $this->secureOnly,
            $this->httpOnly,
            $this->sameSite
        );

        return $cookie->addToResponse($response);
    }

    public function delete(ResponseInterface $response, string $name): ResponseInterface
    {
        $interval         = new \DateInterval('P1Y');
        $interval->invert = true;

        return $this->set($response, $name, '', $interval);
    }
}
