<?php

declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Event\SseHeartbeatEvent;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\MessageBus\OutboundEventTransportInterface;
use BetaKiller\Monitoring\MetricsCollectorInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\PromiseInterface;
use Throwable;

use function React\Promise\resolve;

final class ServerSentEventPingDaemon extends AbstractDaemon
{
    public const CODENAME = 'ServerSentEventPing';

    // Seconds
    private const HEARTBEAT_TIMEOUT  = 15;
    private const HEARTBEAT_INTERVAL = 15;
    private const CHECK_INTERVAL     = self::HEARTBEAT_TIMEOUT * 2;

    /**
     * @var SseHeartbeatEvent[]
     */
    private array $events = [];

    private ?TimerInterface $heartbeatTimer = null;

    private ?TimerInterface $checkTimer = null;

    public function __construct(
        private readonly OutboundEventTransportInterface $outboundTransport,
        private readonly MetricsCollectorInterface $metrics,
        private readonly LoggerInterface $logger
    ) {
    }

    public function startDaemon(LoopInterface $loop): PromiseInterface
    {
        // Emit heartbeat every 5 seconds
        $this->heartbeatTimer = $loop->addPeriodicTimer(self::HEARTBEAT_INTERVAL, function () {
            $sseEvent = new SseHeartbeatEvent();

            $this->outboundTransport->publishOutbound($sseEvent);

            $this->registerEvent($sseEvent);
//            $this->logger->info('Heartbeat sent');
        });

        // Check missing heartbeat events
        $this->checkTimer = $loop->addPeriodicTimer(self::CHECK_INTERVAL, function () {
            $this->checkStaleEvents();

            // Push stat metrics
            $this->metrics->flush();
        });

        $this->outboundTransport->subscribeOutbound(
            SseHeartbeatEvent::getExternalName(),
            fn(SseHeartbeatEvent $event) => $this->proceedEvent($event)
        );

        $this->outboundTransport->startConsuming($loop);

        return resolve();
    }

    public function stopDaemon(LoopInterface $loop): PromiseInterface
    {
        if ($this->heartbeatTimer) {
            $loop->cancelTimer($this->heartbeatTimer);
        }

        if ($this->checkTimer) {
            $loop->cancelTimer($this->checkTimer);
        }

        $this->outboundTransport->stopConsuming($loop);

        return resolve();
    }

    private function proceedEvent(SseHeartbeatEvent $event): PromiseInterface
    {
        $this->markAsProcessing();

        try {
            $ts = $event->getTimestamp();
            $ms = (microtime(true) - $ts) * 1000;

            // Remove event from "pending" list
            $this->removeEvent($event);

            $this->metrics->timing('heartbeat.sse.outbound', $ms);
        } catch (Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        }

        $this->markAsIdle();

        return resolve();
    }

    private function checkStaleEvents(): void
    {
        try {
            $threshold    = microtime(true) - self::HEARTBEAT_TIMEOUT;
            $eventCounter = 0;

            foreach ($this->events as $event) {
                // Skip pending events
                if ($event->getTimestamp() < $threshold) {
                    $eventCounter++;

                    // Remove event from "pending" list (to prevent permanent errors notifications)
                    $this->removeEvent($event);
                }
            }

            if ($eventCounter) {
                $this->logger->warning('SSE has :count/:of stale heartbeat events', [
                    ':count' => $eventCounter,
                    ':of'    => self::CHECK_INTERVAL / self::HEARTBEAT_INTERVAL,
                ]);
            }
        } catch (Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        }
    }

    private function registerEvent(SseHeartbeatEvent $event): void
    {
        $this->events[(string)$event->getTimestamp()] = $event;
    }

    private function removeEvent(SseHeartbeatEvent $event): void
    {
        unset($this->events[(string)$event->getTimestamp()]);
    }
}
