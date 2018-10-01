<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use BetaKiller\Auth\Auth;
use BetaKiller\Helper\UserDetector;
use BetaKiller\Log\LoggerInterface;
use BetaKiller\Session\SessionStorageInterface;
use \Thruway\Authentication\WampCraUserDbInterface;
use Thruway\Common\Utils;

/**
 * https://github.com/voryx/Thruway/tree/master/Examples/Authentication/WampCra
 */
class WampUserDb implements WampCraUserDbInterface
{
    /**
     * @var \BetaKiller\Session\SessionStorageInterface
     */
    private $sessionStorage;

    /**
     * @var \BetaKiller\Helper\UserDetector
     */
    private $userDetector;

    /**
     * @var \BetaKiller\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Auth\Auth
     */
    private $auth;

    /**
     * @param \BetaKiller\Session\SessionStorageInterface $sessionStorage
     * @param \BetaKiller\Auth\Auth                       $auth
     * @param \BetaKiller\Log\LoggerInterface             $logger
     */
    public function __construct(
        SessionStorageInterface $sessionStorage,
        Auth $auth,
        LoggerInterface $logger
    ) {
        $this->sessionStorage = $sessionStorage;
        $this->logger         = $logger;
        $this->auth           = $auth;
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
        $session = $this->sessionStorage->getByID($authid);
        $user    = $this->auth->getSessionUser($session);
        if (!$user) {
            return [];
        }

        return [
            'authid' => $authid,
            'key'    => $session->get('user_agent'),
            'salt'   => Utils::getUniqueId(),//todo can be used?
        ];
    }
}
