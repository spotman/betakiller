<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use BetaKiller\Helper\LoggerHelper;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class EventBus extends AbstractMessageBus implements EventBusInterface
{
    /**
     * @var \BetaKiller\MessageBus\BoundedEventTransportInterface
     */
    private BoundedEventTransportInterface $boundedTransport;

    /**
     * @var \BetaKiller\MessageBus\OutboundEventTransportInterface
     */
    private OutboundEventTransportInterface $outboundTransport;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var \BetaKiller\MessageBus\EventBusConfigInterface
     */
    private EventBusConfigInterface $config;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * EventBus constructor.
     *
     * @param \BetaKiller\MessageBus\EventBusConfigInterface         $config
     * @param \Psr\Container\ContainerInterface                      $container
     * @param \BetaKiller\MessageBus\BoundedEventTransportInterface  $boundedTransport
     * @param \BetaKiller\MessageBus\OutboundEventTransportInterface $outboundTransport
     * @param \Psr\Log\LoggerInterface                               $logger
     */
    public function __construct(
        EventBusConfigInterface         $config,
        ContainerInterface              $container,
        BoundedEventTransportInterface  $boundedTransport,
        OutboundEventTransportInterface $outboundTransport,
        LoggerInterface                 $logger
    ) {
        $this->config            = $config;
        $this->container         = $container;
        $this->boundedTransport  = $boundedTransport;
        $this->outboundTransport = $outboundTransport;
        $this->logger            = $logger;

        $this->initHandlers();
    }

    public static function isMessageAllowedTo(EventMessageInterface $message, RestrictionTargetInterface $target): bool
    {
        return $message instanceof RestrictedMessageInterface
            ? $message->getRestriction()->isSatisfiedBy($target)
            : true;
    }

    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $message
     */
    public function emit(EventMessageInterface $message): void
    {
        // Local processing
        $this->handle($message);

        // Process event inside ESB
        if ($message instanceof BoundedEventMessageInterface) {
            $this->boundedTransport->publishBounded($message);
        }

        // Forward event to other contexts
        if ($message instanceof OutboundEventMessageInterface) {
            $this->outboundTransport->publishOutbound($message);
        }
    }

    protected function getMessageHandlersLimit(): int
    {
        // No limit
        return 0;
    }

    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $message
     */
    private function handle(EventMessageInterface $message): void
    {
        // Wrap event processing with try-catch block to prevent client code crash on missing handlers
        try {
            foreach ($this->getMessageHandlers($message) as $handler) {
                $this->process($message, $handler);
            }
        } catch (Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        }
    }

    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $message
     * @param callable                                     $handler
     */
    private function process(EventMessageInterface $message, callable $handler): void
    {
        // Wrap every message bus processing with try-catch block and log exceptions
        try {
            $handler($message);
        } catch (Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        }
    }

    private function initHandlers(): void
    {
        // For each event
        foreach ($this->config->getEventsMap() as $eventName => $handlers) {
            // Fetch all handlers
            foreach ($handlers as $handlerClassName) {
                // Bind lazy-load wrapper
                $this->on($eventName, function ($event) use ($handlerClassName) {
                    $handler = $this->container->get($handlerClassName);

                    $handler($event);
                });
            }
        }
    }
}
