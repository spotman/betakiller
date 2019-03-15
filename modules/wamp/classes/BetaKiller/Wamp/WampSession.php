<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use Zend\Expressive\Session\SessionIdentifierAwareInterface;
use Zend\Expressive\Session\SessionInterface;

class WampSession
{
    /**
     * @var SessionInterface
     */
    private $userSession;

    /**
     * @var string
     */
    private $authID;

    /**
     * @var \DateTimeImmutable
     */
    private $lastTouched;

    /**
     * WampSession constructor.
     *
     * @param string           $authid
     * @param SessionInterface $userSession
     */
    public function __construct(string $authid, SessionInterface $userSession)
    {
        if (!$userSession instanceof SessionIdentifierAwareInterface) {
            throw new \LogicException('Session must implement SessionIdentifierAwareInterface');
        }

        $this->authID      = $authid;
        $this->userSession = $userSession;

        $this->touched();
    }

    public function getID(): string
    {
        return $this->getUserSession()->getId();
    }

    /**
     * @return string
     */
    public function getAuthID(): string
    {
        return $this->authID;
    }

    /**
     * @return SessionInterface|SessionIdentifierAwareInterface
     */
    public function getUserSession(): SessionInterface
    {
        return $this->userSession;
    }

    public function touched(): void
    {
        $this->lastTouched = new \DateTimeImmutable;
    }

    public function isAlive(): bool
    {
        $now = new \DateTimeImmutable;

        return ($now->getTimestamp() - $this->lastTouched->getTimestamp()) > 600; // 10 minutes timeout
    }
}
