<?php
declare(strict_types=1);

namespace BetaKiller\Session;

use BetaKiller\Model\UserInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionPersistenceInterface;
use Ramsey\Uuid\UuidInterface;

interface SessionStorageInterface extends SessionPersistenceInterface
{
    /**
     * @param \BetaKiller\Session\SessionCause $cause
     * @param string|null                      $userAgent
     * @param string|null                      $id
     *
     * @return \Mezzio\Session\SessionInterface
     */
    public function createSession(SessionCause $cause, ?string $userAgent, ?string $id = null): SessionInterface;

    /**
     * @param string      $token
     * @param string|null $userAgent
     *
     * @return \Mezzio\Session\SessionInterface
     */
    public function getByToken(string $token, string $userAgent = null): SessionInterface;

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
