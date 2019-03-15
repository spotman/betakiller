<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

class WampSessionStorage
{
    /**
     * @var \BetaKiller\Wamp\WampSession[]
     */
    private $sessions = [];

    public function add(WampSession $session): void
    {
        $id = $session->getID();

        if (isset($this->sessions[$id])) {
            throw new \LogicException('Duplicate wamp session '.$id);
        }

        $this->sessions[$id] = $session;
    }

    public function remove(WampSession $session): void
    {
        $id = $session->getID();

        if (!isset($this->sessions[$id])) {
            throw new \LogicException('Missing wamp session '.$id);
        }

        unset($this->sessions[$id]);
    }

    public function findByAuthID(string $authid): ?WampSession
    {
        foreach ($this->sessions as $wampSession) {
            if ($wampSession->getAuthID() === $authid) {
                return $wampSession;
            }
        }

        return null;
    }

    /**
     * @return \BetaKiller\Wamp\WampSession[]
     */
    public function findDeadSessions(): array
    {
        $dead = [];

        foreach ($this->sessions as $wampSession) {
            if ($wampSession->isAlive()) {
                continue;
            }

            $dead[] = $wampSession;
        }

        return $dead;
    }
}
