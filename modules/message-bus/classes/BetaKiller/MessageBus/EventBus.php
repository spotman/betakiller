<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use BetaKiller\Helper\LoggerHelper;
use Psr\Log\LoggerInterface;

class EventBus extends AbstractMessageBus implements EventBusInterface
{
    /**
     * @var \BetaKiller\MessageBus\ExternalEventTransportInterface
     */
    private $transport;

    /**
     * EventBus constructor.
     *
     * @param \BetaKiller\MessageBus\ExternalEventTransportInterface $transport
     * @param \Psr\Log\LoggerInterface                               $logger
     */
    public function __construct(
        ExternalEventTransportInterface $transport,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);

        $this->transport = $transport;
    }

    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $message
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function emit(EventMessageInterface $message): void
    {
        if ($message instanceof OutboundEventMessageInterface) {
            $this->transport->emit($message);
        } else {
            $this->handle($message);
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
            LoggerHelper::logException($this->logger, $e);
        }
    }
}
