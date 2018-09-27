<?php
declare(strict_types=1);

namespace BetaKiller\Session;

use BetaKiller\Model\UserInterface;
use Zend\Expressive\Session\SessionInterface;

class SessionUserAdapter
{
    public static function getUser(SessionInterface $session): UserInterface
    {
        return $session->get('auth_user');
    }
}
