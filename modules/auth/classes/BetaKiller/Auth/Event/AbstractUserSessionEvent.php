<?php
declare(strict_types=1);

namespace BetaKiller\Auth\Event;

use BetaKiller\MessageBus\OutboundEventMessageInterface;
use Mezzio\Session\SessionIdentifierAwareInterface;
use Mezzio\Session\SessionInterface;

// WAMP/Websocket daemon needs this events
abstract class AbstractUserSessionEvent implements OutboundEventMessageInterface
{
    /**
     * @var SessionInterface|SessionIdentifierAwareInterface
     */
    private $session;

    /**
     * AbstractUserSessionEvent constructor.
     *
     * @param \Mezzio\Session\SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
      if (!$session instanceof SessionIdentifierAwareInterface) {
        throw new \InvalidArgumentException();
      }

        $this->session = $session;
    }

    /**
     * @return array|null
     */
    public function getOutboundData(): ?array
    {
        return [
            'session' => $this->session->getId(),
        ];
    }
}
