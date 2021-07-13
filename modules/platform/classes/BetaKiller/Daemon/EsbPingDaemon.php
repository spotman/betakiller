<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Event\HeartbeatBoundedEvent;
use BetaKiller\Event\HeartbeatOutboundEvent;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\MessageBus\BoundedEventTransportInterface;
use BetaKiller\MessageBus\OutboundEventTransportInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\PromiseInterface;
use Throwable;
use Thruway\Logging\Logger;
use function React\Promise\resolve;

/**
 * Class WampEsbBridgeDaemon
 *
 * @package BetaKiller\Daemon
 */
final class EsbPingDaemon extends AbstractDaemon
{
    public const CODENAME = 'EsbPing';

    // Seconds
    private const HEARTBEAT_TIMEOUT  = 15;
    private const HEARTBEAT_INTERVAL = 5;
    private const CHECK_INTERVAL     = self::HEARTBEAT_TIMEOUT * 2;

    /**
     * @var \BetaKiller\MessageBus\OutboundEventTransportInterface
     */
    private OutboundEventTransportInterface $outboundTransport;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var \BetaKiller\MessageBus\BoundedEventTransportInterface
     */
    private BoundedEventTransportInterface $boundedTransport;

    /**
     * @var HeartbeatBoundedEvent[]
     */
    private array $boundedEvents = [];

    /**
     * @var HeartbeatOutboundEvent[]
     */
    private array $outboundEvents = [];

    private TimerInterface $heartbeatTimer;

    private TimerInterface $checkTimer;

    /**
     * @param \BetaKiller\MessageBus\BoundedEventTransportInterface  $boundedTransport
     * @param \BetaKiller\MessageBus\OutboundEventTransportInterface $outboundTransport
     * @param \Psr\Log\LoggerInterface                               $logger
     */
    public function __construct(
        BoundedEventTransportInterface $boundedTransport,
        OutboundEventTransportInterface $outboundTransport,
        LoggerInterface $logger
    ) {
        $this->outboundTransport = $outboundTransport;
        $this->boundedTransport  = $boundedTransport;
        $this->logger            = $logger;
    }

    public function startDaemon(LoopInterface $loop): PromiseInterface
    {
        Logger::set($this->logger);

        // Emit heartbeat every 5 seconds
        $this->heartbeatTimer = $loop->addPeriodicTimer(self::HEARTBEAT_INTERVAL, function () {
            $boundedEvent  = new HeartbeatBoundedEvent;
            $outboundEvent = new HeartbeatOutboundEvent;

            $this->boundedEvents[$boundedEvent->getTimestamp()]   = $boundedEvent;
            $this->outboundEvents[$outboundEvent->getTimestamp()] = $outboundEvent;

            $this->boundedTransport->publishBounded($boundedEvent);
            $this->outboundTransport->publishOutbound($outboundEvent);

            $this->logger->info('Heartbeat sent');
        });

        // Check missing heartbeat events
        $this->checkTimer = $loop->addPeriodicTimer(self::CHECK_INTERVAL, function () {
            $this->checkStaleEvents();
        });

        $this->boundedTransport->subscribeBounded(
            HeartbeatBoundedEvent::getExternalName(),
            function (HeartbeatBoundedEvent $event) {
                return $this->proceedBoundedEvent($event);
            }
        );

        $this->outboundTransport->subscribeOutbound(
            HeartbeatOutboundEvent::getExternalName(),
            function (HeartbeatOutboundEvent $event) {
                return $this->proceedOutboundEvent($event);
            });

        $this->boundedTransport->startConsuming($loop);
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

        $this->boundedTransport->stopConsuming($loop);
        $this->outboundTransport->stopConsuming($loop);

        return resolve();
    }

    private function proceedBoundedEvent(HeartbeatBoundedEvent $event): PromiseInterface
    {
        try {
            $ts = $event->getTimestamp();

            $this->logger->info('Bounded heartbeat received in :ms ms', [
                ':ms' => (int)((microtime(true) - $ts) * 1000),
            ]);

            // Remove event from "pending" list
            unset($this->boundedEvents[$ts]);
        } catch (Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        }

        return resolve();
    }

    private function proceedOutboundEvent(HeartbeatOutboundEvent $event): PromiseInterface
    {
        try {
            $ts = $event->getTimestamp();

            $this->logger->info('Outbound heartbeat received in :ms ms', [
                ':ms' => (int)((microtime(true) - $ts) * 1000),
            ]);

            // Remove event from "pending" list
            unset($this->outboundEvents[$ts]);
        } catch (Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        }

        return resolve();
    }

    private function checkStaleEvents(): void
    {
        try {
            $threshold      = time() - self::HEARTBEAT_TIMEOUT;
            $boundedCounter = $outboundCounter = 0;

            foreach ($this->boundedEvents as $ts => $event) {
                // Skip pending events
                if ($ts < $threshold) {
                    $boundedCounter++;

                    // Remove event from "pending" list (to prevent permanent errors notifications)
                    unset($this->boundedEvents[$ts]);
                }
            }

            foreach ($this->outboundEvents as $ts => $event) {
                // Skip pending events
                if ($ts < $threshold) {
                    $outboundCounter++;

                    // Remove event from "pending" list (to prevent permanent errors notifications)
                    unset($this->outboundEvents[$ts]);
                }
            }

            if ($boundedCounter) {
                $this->logger->warning('ESB has :count/:of stale bounded heartbeat events', [
                    ':count' => $boundedCounter,
                    ':of'    => self::CHECK_INTERVAL / self::HEARTBEAT_INTERVAL,
                ]);
            }

            if ($outboundCounter) {
                $this->logger->warning('ESB has :count/:of stale outbound heartbeat events', [
                    ':count' => $outboundCounter,
                    ':of'    => self::CHECK_INTERVAL / self::HEARTBEAT_INTERVAL,
                ]);
            }
        } catch (Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        }
    }
}
