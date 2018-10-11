<?php
declare(strict_types=1);

namespace BetaKiller\Session;

use BetaKiller\Model\UserInterface;
use Zend\Expressive\Session\SessionInterface;
use Zend\Expressive\Session\SessionPersistenceInterface;

interface SessionStorageInterface extends SessionPersistenceInterface
{
    /**
     * @param string $id
     *
     * @return \Zend\Expressive\Session\SessionInterface
     */
    public function getByToken(string $id): SessionInterface;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \Zend\Expressive\Session\SessionInterface[]
     */
    public function getUserSessions(UserInterface $user): array;
}
