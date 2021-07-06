<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception\SecurityException;
use HansOtt\PSR7Cookies\Cookie;
use HansOtt\PSR7Cookies\RequestCookies;
use HansOtt\PSR7Cookies\SetCookie;
use HansOtt\PSR7Cookies\Signer;
use HansOtt\PSR7Cookies\Signer\Hmac\Sha512;
use HansOtt\PSR7Cookies\Signer\Key;
use HansOtt\PSR7Cookies\Signer\Mismatch;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CookieHelper
{
    /**
     * @var \HansOtt\PSR7Cookies\Signer
     */
    private Signer $signer;

    /**
     * @var Key
     */
    private Key $key;

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
     * @param \BetaKiller\Helper\AppEnvInterface    $appEnv
     *
     * @throws \HansOtt\PSR7Cookies\InvalidArgumentException
     */
    public function __construct(AppConfigInterface $appConfig, AppEnvInterface $appEnv)
    {
        if ($appConfig->isSecure() && !$appEnv->isInternalWebServer()) {
            $this->secureOnly = true;
        }

        $this->signer = new Sha512();

        $key = $appEnv->getEnvVariable('COOKIE_ENCRYPT_KEY', true);

        $this->key = new Key($key);
    }

    public function getAll(ServerRequestInterface $request): array
    {
        $cookies = $this->getCookies($request);

        $data = [];

        foreach ($cookies as $cookie) {
            try {
                $data[$cookie->getName()] = $this->decode($cookie);
            } /** @noinspection BadExceptionsProcessingInspection */
            catch (Mismatch $e) {
                // Skip tempered/client-side cookies
            }
        }

        return $data;
    }

    public function get(ServerRequestInterface $request, string $name): ?string
    {
        $cookies = $this->getCookies($request);

        if (!$cookies->has($name)) {
            return null;
        }

        $cookie = $cookies->get($name);

        try {
            return $this->decode($cookie);
        } catch (Mismatch $e) {
            // Cookie is tampered!
            throw new SecurityException('Cookie ":name" is tampered from IP :ip', [
                ':name' => $cookie->getName(),
                ':ip'   => ServerRequestHelper::getIpAddress($request),
            ], 0, $e);
        }
    }

    public function decodeValue(string $name, string $value): string
    {
        $cookie = new Cookie($name, $value);

        return $this->decode($cookie);
    }

    public function encodeValue(string $name, string $value): string
    {
        $cookie = new SetCookie($name, $value);

        return $this->signer->sign($cookie, $this->key)->getValue();
    }

    private function getCookies(ServerRequestInterface $request): RequestCookies
    {
        return RequestCookies::createFromRequest($request);
    }

    private function decode(Cookie $cookie): string
    {
        return $this->signer->verify($cookie, $this->key)->getValue();
    }

    public function set(
        ResponseInterface $response,
        string $name,
        string $value,
        \DateInterval $expiresIn
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

        $cookie = $this->signer->sign($cookie, $this->key);

        return $cookie->addToResponse($response);
    }

    public function delete(ResponseInterface $response, string $name): ResponseInterface
    {
        $interval         = new \DateInterval('P1Y');
        $interval->invert = true;

        return $this->set($response, $name, '', $interval);
    }
}
