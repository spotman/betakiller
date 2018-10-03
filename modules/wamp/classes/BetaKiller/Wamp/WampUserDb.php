<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use BetaKiller\Auth\AuthFacade;
use BetaKiller\Session\SessionStorageInterface;
use Thruway\Authentication\WampCraUserDbInterface;
//use Thruway\Common\Utils;

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
     * @var \BetaKiller\Auth\AuthFacade
     */
    private $auth;

    /**
     * @param \BetaKiller\Session\SessionStorageInterface $sessionStorage
     * @param \BetaKiller\Auth\AuthFacade                 $auth
     */
    public function __construct(SessionStorageInterface $sessionStorage, AuthFacade $auth)
    {
        $this->sessionStorage = $sessionStorage;
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

        $userAgent = $session->get(AuthFacade::SESSION_USER_AGENT);

        return [
            'authid' => $authid,
            'key'    => $userAgent,
            'salt'   => null, // Utils::getDerivedKey($userAgent, $authid)
        ];
    }
}
