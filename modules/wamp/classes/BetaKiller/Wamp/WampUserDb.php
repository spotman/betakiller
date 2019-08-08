<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use BetaKiller\Helper\CookieHelper;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Service\AuthService;
use BetaKiller\Session\DatabaseSessionStorage;
use LogicException;
use Psr\Log\LoggerInterface;
use Throwable;
use Thruway\Authentication\WampCraUserDbInterface;

//use Thruway\Common\Utils;

/**
 * https://github.com/voryx/Thruway/tree/master/Examples/Authentication/WampCra
 */
class WampUserDb implements WampCraUserDbInterface
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Service\AuthService
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
     * @param \BetaKiller\Service\AuthService $auth
     * @param \BetaKiller\Helper\CookieHelper $cookieHelper
     * @param \Psr\Log\LoggerInterface        $logger
     */
    public function __construct(AuthService $auth, CookieHelper $cookieHelper, LoggerInterface $logger)
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

            $session = $this->auth->getSession($sessionId);

            if (!SessionHelper::isPersistent($session)) {
                throw new LogicException('Using non-persistent session '.$sessionId);
            }

// No user agent checks anymore (inconsistent behaviour)
//            $userAgent = SessionHelper::getUserAgent($session);
//
//            if (!$userAgent) {
//                throw new \LogicException('Missing user-agent in session data '.$sessionId);
//            }

// Allow quests to use WAMP on landing pages
//            if (!SessionHelper::getUserID($session)) {
//                throw new \LogicException('Guest connection to wamp from session '.$sessionId);
//            }

            return $this->makeData($authid, $authid);
        } catch (Throwable $e) {
            $this->logException($this->logger, $e);

            // Make random key string so auth will never be succeeded
            return $this->makeFakeData($authid);
        }
    }

    private function makeData(string $authID, string $key): array
    {
        return [
            'authid' => $authID,
            'key'    => $key,
            'salt'   => null, // Utils::getDerivedKey($userAgent, $authid)
        ];
    }

    private function makeFakeData(string $authid): array
    {
        // Make random key string so auth will never be succeeded
        return $this->makeData($authid, sha1(microtime()));
    }
}
