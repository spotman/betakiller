<?php
declare(strict_types=1);

namespace BetaKiller\Session;

use BetaKiller\Model\UserInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionPersistenceInterface;

interface SessionStorageInterface extends SessionPersistenceInterface
{
    /**
     * @param string|null $originUrl
     *
     * @return \Mezzio\Session\SessionInterface
     */
    public function createSession(string $originUrl = null): SessionInterface;

    /**
     * @param string $token
     *
     * @return \Mezzio\Session\SessionInterface
     */
    public function getByToken(string $token): SessionInterface;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \Mezzio\Session\SessionInterface[]
     */
    public function getUserSessions(UserInterface $user): array;

    /**
     * @param \Mezzio\Session\SessionInterface $session
     */
    public function destroySession(SessionInterface $session): void;
}
