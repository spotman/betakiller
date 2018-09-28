<?php
declare(strict_types=1);

namespace BetaKiller\Session;

use Zend\Expressive\Session\SessionIdentifierAwareInterface;

interface SessionInterface extends \Zend\Expressive\Session\SessionInterface, SessionIdentifierAwareInterface
{
}
