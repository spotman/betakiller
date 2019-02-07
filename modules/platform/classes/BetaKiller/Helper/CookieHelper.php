<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception\SecurityException;
use HansOtt\PSR7Cookies\Cookie;
use HansOtt\PSR7Cookies\RequestCookies;
use HansOtt\PSR7Cookies\SetCookie;
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
    private $signer;

    /**
     * @var Key
     */
    private $key;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $path = '/';

    /**
     * @var bool
     */
    private $secureOnly = false;

    /**
     * Session ID cookie is used for WAMP auth
     *
     * @var bool
     */
    private $httpOnly = false;

    /**
     * CookieHelper constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     *
     * @throws \HansOtt\PSR7Cookies\InvalidArgumentException
     */
    public function __construct(AppConfigInterface $appConfig)
    {
        if ($appConfig->isSecure()) {
            $this->secureOnly = true;
        }

        $this->domain = $appConfig->getBaseUri()->getHost();

        $this->signer = new Sha512();

        $key = getenv('COOKIE_ENCRYPT_KEY');

        if (!$key) {
            throw new \UnexpectedValueException('Cookie encryption key must be set via COOKIE_ENCRYPT_KEY env var');
        }

        $this->key = new Key($key);
    }

    public function getAll(ServerRequestInterface $request): array
    {
        $cookies = $this->getCookies($request);

        $data = [];

        foreach ($cookies as $cookie) {
            try {
                $data[$cookie->getName()] = $this->decode($cookie)->getValue();
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
            return $this->decode($cookie)->getValue();
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

        return $this->decode($cookie)->getValue();
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

    private function decode(Cookie $cookie): Cookie
    {
        return $this->signer->verify($cookie, $this->key);
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
            $this->domain,
            $this->secureOnly,
            $this->httpOnly
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
