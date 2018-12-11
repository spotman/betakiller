<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use BetaKiller\Auth\AuthFacade;
use BetaKiller\Helper\CookieHelper;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Session\DatabaseSessionStorage;
use Psr\Log\LoggerInterface;
use Thruway\Authentication\WampCraUserDbInterface;

//use Thruway\Common\Utils;

/**
 * https://github.com/voryx/Thruway/tree/master/Examples/Authentication/WampCra
 */
class WampUserDb implements WampCraUserDbInterface
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Auth\AuthFacade
     */
    private $auth;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Helper\CookieHelper
     */
    private $cookieHelper;

    /**
     * @param \BetaKiller\Auth\AuthFacade     $auth
     * @param \BetaKiller\Helper\CookieHelper $cookieHelper
     * @param \Psr\Log\LoggerInterface        $logger
     */
    public function __construct(AuthFacade $auth, CookieHelper $cookieHelper, LoggerInterface $logger)
    {
        $this->auth         = $auth;
        $this->logger       = $logger;
        $this->cookieHelper = $cookieHelper;
    }

    /**
     * This should take a authid string as the argument and return
     * an associative array with authid, key, and salt.
     *
     * If salt is non-null, the key is the salted version of the password.
     *
     * @param string $authid
     *
     * @return mixed
     */
    public function get($authid)
    {
        try {
            // Session cookie is encoded
            $this->logger->debug('Decoding sid ":value"', [':value' => $authid]);
            $sessionId = $this->cookieHelper->decodeValue(DatabaseSessionStorage::COOKIE_NAME, $authid);

            $this->logger->debug('Sid decoded to ":value"', [':value' => $sessionId]);

            $session   = $this->auth->getSession($sessionId);
            $userAgent = SessionHelper::getUserAgent($session);

            return $this->makeData($authid, $userAgent);
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);

            // Make random user agent string so auth will never be succeeded
            return $this->makeData($authid, \sha1(\microtime()));
        }
    }

    private function makeData(string $authID, string $userAgent): array
    {
        return [
            'authid' => $authID,
            'key'    => $userAgent,
            'salt'   => null, // Utils::getDerivedKey($userAgent, $authid)
        ];
    }
}
