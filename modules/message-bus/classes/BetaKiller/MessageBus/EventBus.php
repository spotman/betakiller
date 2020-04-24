<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use BetaKiller\Helper\LoggerHelper;
use Psr\Log\LoggerInterface;

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
     * EventBus constructor.
     *
     * @param \BetaKiller\MessageBus\BoundedEventTransportInterface  $boundedTransport
     * @param \BetaKiller\MessageBus\OutboundEventTransportInterface $outboundTransport
     * @param \Psr\Log\LoggerInterface                               $logger
     */
    public function __construct(
        BoundedEventTransportInterface $boundedTransport,
        OutboundEventTransportInterface $outboundTransport,
        LoggerInterface $logger
    ) {
        $this->boundedTransport  = $boundedTransport;
        $this->outboundTransport = $outboundTransport;
        $this->logger            = $logger;
    }

    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $message
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function emit(EventMessageInterface $message): void
    {
        // Local processing
        $this->handle($message);

        // Process event inside of ESB
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
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    private function handle(EventMessageInterface $message): void
    {
        foreach ($this->getMessageHandlers($message) as $handler) {
            $this->process($message, $handler);
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
        } catch (\Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        }
    }
}
