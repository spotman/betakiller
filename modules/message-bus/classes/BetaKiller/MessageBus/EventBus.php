<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use BetaKiller\Helper\LoggerHelper;
use Psr\Container\ContainerInterface;
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
     * @param \Psr\Container\ContainerInterface                      $container
     * @param \BetaKiller\MessageBus\ExternalEventTransportInterface $transport
     * @param \Psr\Log\LoggerInterface                               $logger
     */
    public function __construct(
        ContainerInterface $container,
        ExternalEventTransportInterface $transport,
        LoggerInterface $logger
    ) {
        parent::__construct($container, $logger);

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
        foreach ($this->getMessageHandlersClassNames($message) as $handlersClassName) {
            $handler = $this->getHandlerInstance($handlersClassName);
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
