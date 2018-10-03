<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use BetaKiller\Auth\AuthFacade;
use Thruway\Authentication\WampCraUserDbInterface;
//use Thruway\Common\Utils;

/**
 * https://github.com/voryx/Thruway/tree/master/Examples/Authentication/WampCra
 */
class WampUserDb implements WampCraUserDbInterface
{
    /**
     * @var \BetaKiller\Auth\AuthFacade
     */
    private $auth;

    /**
     * @param \BetaKiller\Auth\AuthFacade $auth
     */
    public function __construct(AuthFacade $auth)
    {
        $this->auth = $auth;
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
        $user    = $this->auth->getUserFromSessionID($authid);
        $session = $this->auth->getUserSession($user);

        $userAgent = $session->get(AuthFacade::SESSION_USER_AGENT);

        return [
            'authid' => $authid,
            'key'    => $userAgent,
            'salt'   => null, // Utils::getDerivedKey($userAgent, $authid)
        ];
    }
}
