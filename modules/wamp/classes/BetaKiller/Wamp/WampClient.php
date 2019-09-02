<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

/**
 * https://github.com/voryx/Thruway#php-client-example
 */
class WampClient extends \Thruway\Peer\Client
{
    private $onCloseHandlers = [];

    /**
     * Handle end session
     *
     * @param \Thruway\ClientSession $session
     */
    public function onSessionEnd($session)
    {
        parent::onSessionEnd($session);

        foreach ($this->onCloseHandlers as $handler) {
            $handler($session);
        }
    }

    public function onSessionClose(callable $callback): void
    {
        $this->onCloseHandlers[] = $callback;
    }
}
