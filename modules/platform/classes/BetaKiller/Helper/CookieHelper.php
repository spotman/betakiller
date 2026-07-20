<?php

declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Env\AppEnvInterface;
use DateInterval;
use DateTimeImmutable;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\Modifier\SameSite;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CookieHelper
{
    /**
     * @var string
     */
    private string $domain;

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
     * @var bool
     */
    private bool $httpOnly = true;

    /**
     * CookieHelper constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     * @param \BetaKiller\Env\AppEnvInterface       $appEnv
     */
    public function __construct(AppConfigInterface $appConfig, AppEnvInterface $appEnv)
    {
        if ($appConfig->isSecure() && !$appEnv->isInternalWebServer()) {
            $this->secureOnly = true;
        }

        $this->domain = $appConfig->getBaseUri()->getHost();
    }

    public static function has(ServerRequestInterface $request, string $name): bool
    {
        return isset($request->getCookieParams()[$name]);
    }

    public static function get(ServerRequestInterface $request, string $name): string
    {
        return $request->getCookieParams()[$name];
    }

    public function set(
        ResponseInterface $response,
        string $name,
        string $value,
        DateInterval $expiresIn
    ): ResponseInterface {
        $dt        = new DateTimeImmutable();
        $expiresAt = $dt->setTimezone(DateTimeHelper::getUtcTimezone())->add($expiresIn);

        // No domain defined => lock cookies to current domain only
        $cookie = SetCookie::create($name, $value)
            ->withExpires($expiresAt)
            ->withDomain($this->domain)
            ->withPath($this->path)
            ->withSecure($this->secureOnly)
            ->withHttpOnly($this->httpOnly)
            ->withSameSite(SameSite::fromString($this->sameSite));

        return FigResponseCookies::set($response, $cookie);
    }

    public function delete(ResponseInterface $response, string $name): ResponseInterface
    {
        $interval         = new DateInterval('P1Y');
        $interval->invert = true;

        return $this->set($response, $name, '', $interval);
    }
}
