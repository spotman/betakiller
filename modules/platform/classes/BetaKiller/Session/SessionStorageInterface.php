<?php
declare(strict_types=1);

namespace BetaKiller\Session;

use Zend\Expressive\Session\SessionInterface;
use Zend\Expressive\Session\SessionPersistenceInterface;

interface SessionStorageInterface extends SessionPersistenceInterface
{
    public function getByID(string $id): SessionInterface;
}
