<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use BetaKiller\Auth\AuthFacade;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\SessionHelper;
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
     * @param \BetaKiller\Auth\AuthFacade $auth
     * @param \Psr\Log\LoggerInterface    $logger
     */
    public function __construct(AuthFacade $auth, LoggerInterface $logger)
    {
        $this->auth   = $auth;
        $this->logger = $logger;
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
            $session   = $this->auth->getSession($authid);
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
