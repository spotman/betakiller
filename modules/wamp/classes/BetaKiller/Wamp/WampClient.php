<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use BetaKiller\Exception;
use Thruway\ClientSession;
use Thruway\Logging\Logger;

/**
 * https://github.com/voryx/Thruway#php-client-example
 */
class WampClient extends \Thruway\Peer\Client
{
    public const RPC_PING = 'com.wn.ping';

    /**
     * @var callable[]
     */
    private array $onCloseHandlers = [];

    /**
     * @var \React\EventLoop\TimerInterface[]
     */
    private array $pingTimers = [];

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

    public function onSessionOpen(callable $callback): void
    {
        $this->on('open', $callback);
    }

    public function onSessionClose(callable $callback): void
    {
        $this->onCloseHandlers[] = $callback;
    }

    public function bindPingHandlers(): void
    {
        $loop = $this->getLoop();

        $this->onSessionOpen(function (ClientSession $session) use ($loop) {
            $id = $session->getSessionId();

            if (isset($this->pingTimers[$id])) {
                throw new Exception('WAMP session ":id" ping timer already set', [
                    ':id' => $id,
                ]);
            }

            // Ping every 30 seconds to keep session opened
            $this->pingTimers[$id] = $loop->addPeriodicTimer(30, static function ($pingTimer) use ($loop, $session) {
                $timeoutTimer = $loop->addTimer(10, static function () use ($pingTimer, $loop, $session) {
                    $loop->cancelTimer($pingTimer);
                    Logger::notice(null, sprintf('WAMP session "%s" ping timed out', $session->getSessionId()));
                });

                // No ping implementation in PawlTransport, so using a custom ping RPC method here
                $session->call(self::RPC_PING)
                    ->otherwise(static function () use ($session) {
                        Logger::notice(null, sprintf('WAMP session "%s" ping failed', $session->getSessionId()));
                    })
                    ->always(static function () use ($timeoutTimer, $loop) {
                        $loop->cancelTimer($timeoutTimer);
                    });
            });
        });

        $this->onSessionClose(function (ClientSession $session) {
            $id = $session->getSessionId();

            // Session is terminated, no ID at this point, can not stop ping timer
            if (!$id) {
                return;
            }

            if (!isset($this->pingTimers[$id])) {
                throw new Exception('WAMP session ":id" ping timer is missing', [
                    ':id' => $id,
                ]);
            }

            $this->getLoop()->cancelTimer($this->pingTimers[$id]);

            unset($this->pingTimers[$id]);
        });
    }
}
