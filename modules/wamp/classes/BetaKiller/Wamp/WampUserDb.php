<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use \Thruway\Authentication\WampCraUserDbInterface;

/**
 * https://github.com/voryx/Thruway/tree/master/Examples/Authentication/WampCra
 */
class WampUserDb implements WampCraUserDbInterface
{
    /**
     * @var \Session_Database
     */
    private $sessionDatabase;

    /**
     * @param \Session_Database $sessionDatabase
     */
    public function __construct(SessionStorageInterface $sessionDatabase)
    {
        $this->sessionDatabase = $sessionDatabase;
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
        $salt = '';

        return [
            'authid' => $this->sessionDatabase->id(),
            'key'    => $this->sessionDatabase->get('user_agent'),
            'salt'   => $salt,
        ];
    }
}
