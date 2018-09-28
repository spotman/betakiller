<?php
declare(strict_types=1);

namespace BetaKiller\Session;

use Zend\Expressive\Session\SessionPersistenceInterface;

interface SessionStorageInterface extends SessionPersistenceInterface
{
    /**
     * @param string $id
     *
     * @return \BetaKiller\Session\SessionInterface
     */
    public function getByID(string $id): SessionInterface;
}
