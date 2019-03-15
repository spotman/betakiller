<?php
declare(strict_types=1);

namespace BetaKiller\Auth\Event;

use BetaKiller\MessageBus\EventMessageInterface;
use Zend\Expressive\Session\SessionInterface;

abstract class AbstractUserSessionEvent implements EventMessageInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * AbstractUserSessionEvent constructor.
     *
     * @param \Zend\Expressive\Session\SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @return SessionInterface
     */
    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    /**
     * Must return true if message requires processing in external message queue (instead of internal queue)
     *
     * @return bool
     */
    public function isExternal(): bool
    {
        // WAMP/Websocket daemon needs this events
        return true;
    }

    /**
     * Must return true if message requires at least one handler to be processed
     *
     * @return bool
     */
    public function handlersRequired(): bool
    {
        return false;
    }
}
