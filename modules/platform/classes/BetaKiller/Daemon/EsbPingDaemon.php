<?php

declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Event\HeartbeatBoundedEvent;
use BetaKiller\Event\HeartbeatOutboundEvent;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\MessageBus\BoundedEventTransportInterface;
use BetaKiller\MessageBus\OutboundEventTransportInterface;
use BetaKiller\Monitoring\MetricsCollectorInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\PromiseInterface;
use Throwable;

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
    private const HEARTBEAT_INTERVAL = 1;
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

    private ?TimerInterface $heartbeatTimer = null;

    private ?TimerInterface $checkTimer = null;

    /**
     * @var \BetaKiller\Monitoring\MetricsCollectorInterface
     */
    private MetricsCollectorInterface $metrics;

    /**
     * @param \BetaKiller\MessageBus\BoundedEventTransportInterface  $boundedTransport
     * @param \BetaKiller\MessageBus\OutboundEventTransportInterface $outboundTransport
     * @param \BetaKiller\Monitoring\MetricsCollectorInterface       $metrics
     * @param \Psr\Log\LoggerInterface                               $logger
     */
    public function __construct(
        BoundedEventTransportInterface $boundedTransport,
        OutboundEventTransportInterface $outboundTransport,
        MetricsCollectorInterface $metrics,
        LoggerInterface $logger
    ) {
        $this->outboundTransport = $outboundTransport;
        $this->boundedTransport  = $boundedTransport;
        $this->metrics           = $metrics;
        $this->logger            = $logger;
    }

    public function startDaemon(LoopInterface $loop): PromiseInterface
    {
        // Emit heartbeat every 5 seconds
        $this->heartbeatTimer = $loop->addPeriodicTimer(self::HEARTBEAT_INTERVAL, function () {
            $boundedEvent  = new HeartbeatBoundedEvent();
            $outboundEvent = new HeartbeatOutboundEvent();

            $this->boundedTransport->publishBounded($boundedEvent);
            $this->outboundTransport->publishOutbound($outboundEvent);

            $this->registerBoundedEvent($boundedEvent);
            $this->registerOutboundEvent($outboundEvent);
//            $this->logger->info('Heartbeat sent');
        });

        // Check missing heartbeat events
        $this->checkTimer = $loop->addPeriodicTimer(self::CHECK_INTERVAL, function () {
            $this->checkStaleEvents();

            // Push stat metrics
            $this->metrics->flush();
        });

        $this->boundedTransport->subscribeBounded(
            HeartbeatBoundedEvent::getExternalName(),
            fn(HeartbeatBoundedEvent $event) => $this->proceedBoundedEvent($event)
        );

        $this->outboundTransport->subscribeOutbound(
            HeartbeatOutboundEvent::getExternalName(),
            fn(HeartbeatOutboundEvent $event) => $this->proceedOutboundEvent($event)
        );

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
        $this->markAsProcessing();

        try {
            $ts = $event->getTimestamp();
            $ms = (microtime(true) - $ts) * 1000;

            // Remove event from "pending" list
            $this->removeBoundedEvent($event);

            $this->metrics->timing('heartbeat.esb.bounded', $ms);
        } catch (Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        }

        $this->markAsIdle();

        return resolve();
    }

    private function proceedOutboundEvent(HeartbeatOutboundEvent $event): PromiseInterface
    {
        $this->markAsProcessing();

        try {
            $ts = $event->getTimestamp();
            $ms = (microtime(true) - $ts) * 1000;

            // Remove event from "pending" list
            $this->removeOutboundEvent($event);

            $this->metrics->timing('heartbeat.esb.outbound', $ms);
        } catch (Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        }

        $this->markAsIdle();

        return resolve();
    }

    private function checkStaleEvents(): void
    {
        try {
            $threshold      = microtime(true) - self::HEARTBEAT_TIMEOUT;
            $boundedCounter = $outboundCounter = 0;

            foreach ($this->boundedEvents as $event) {
                // Skip pending events
                if ($event->getTimestamp() < $threshold) {
                    $boundedCounter++;

                    // Remove event from "pending" list (to prevent permanent errors notifications)
                    $this->removeBoundedEvent($event);
                }
            }

            foreach ($this->outboundEvents as $event) {
                // Skip pending events
                if ($event->getTimestamp() < $threshold) {
                    $outboundCounter++;

                    // Remove event from "pending" list (to prevent permanent errors notifications)
                    $this->removeOutboundEvent($event);
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

    private function registerBoundedEvent(HeartbeatBoundedEvent $event): void
    {
        $this->boundedEvents[(string)$event->getTimestamp()] = $event;
    }

    private function registerOutboundEvent(HeartbeatOutboundEvent $event): void
    {
        $this->outboundEvents[(string)$event->getTimestamp()] = $event;
    }

    private function removeBoundedEvent(HeartbeatBoundedEvent $event): void
    {
        unset($this->boundedEvents[(string)$event->getTimestamp()]);
    }

    private function removeOutboundEvent(HeartbeatOutboundEvent $event): void
    {
        unset($this->outboundEvents[(string)$event->getTimestamp()]);
    }
}
