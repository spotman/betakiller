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
    private $path;

    /**
     * @var bool
     */
    private $secureOnly = false;

    /**
     * @var bool
     */
    private $httpOnly = true;

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

        $baseUri = $appConfig->getBaseUri();

        $this->domain = $baseUri->getHost();
        $this->path   = $baseUri->getPath();

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
            $data[$cookie->getName()] = $this->verify($cookie, $request)->getValue();
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

        return $this->verify($cookie, $request)->getValue();
    }

    private function getCookies(ServerRequestInterface $request): RequestCookies
    {
        return RequestCookies::createFromRequest($request);
    }

    private function verify(Cookie $cookie, ServerRequestInterface $request): Cookie
    {
        try {

            return $this->signer->verify($cookie, $this->key);
        } catch (Mismatch $e) {
            // Cookie is tampered!
            throw new SecurityException('Cookie ":name" is tampered from IP :ip', [
                ':name' => $cookie->getName(),
                ':ip'   => ServerRequestHelper::getIpAddress($request),
            ], 0, $e);
        }
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
