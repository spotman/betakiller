<?php
declare(strict_types=1);

namespace BetaKiller\Session;

use BetaKiller\Model\UserInterface;
use Zend\Expressive\Session\SessionInterface;
use Zend\Expressive\Session\SessionPersistenceInterface;

interface SessionStorageInterface extends SessionPersistenceInterface
{
    /**
     * @param string $originUrl
     *
     * @return \Zend\Expressive\Session\SessionInterface
     */
    public function createSession(string $originUrl = null): SessionInterface;

    /**
     * @param string $token
     *
     * @return \Zend\Expressive\Session\SessionInterface
     */
    public function getByToken(string $token): SessionInterface;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \Zend\Expressive\Session\SessionInterface[]
     */
    public function getUserSessions(UserInterface $user): array;

    /**
     * @param \Zend\Expressive\Session\SessionInterface $session
     */
    public function destroySession(SessionInterface $session): void;
}
