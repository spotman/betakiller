<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use BetaKiller\Helper\UserDetector;
use BetaKiller\Log\LoggerInterface;
use BetaKiller\Session\SessionStorageInterface;
use \Thruway\Authentication\WampCraUserDbInterface;

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
     * @param \BetaKiller\Session\SessionStorageInterface $sessionStorage
     * @param \BetaKiller\Helper\UserDetector             $userDetector
     * @param \BetaKiller\Log\LoggerInterface             $logger
     */
    public function __construct(
        SessionStorageInterface $sessionStorage,
        UserDetector $userDetector,
        LoggerInterface $logger
    )
    {
        $this->sessionStorage = $sessionStorage;
        $this->userDetector   = $userDetector;
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
        $salt = null;

        $session = $this->sessionStorage->getByID($authid);
        $user    = $this->userDetector->fromSession($session);
        if (!$user->getID()) {
            return [];
        }

        return [
            'authid' => $authid,
            'key'    => $session->get('user_agent'),
            'salt'   => $salt,
        ];
    }
}
