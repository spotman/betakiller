<?php
namespace BetaKiller\MessageBus;

class CommandBus extends AbstractMessageBus implements CommandBusInterface
{
    /**
     * @param \BetaKiller\MessageBus\CommandMessageInterface $message
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function run(CommandMessageInterface $message): void
    {
        $this->handle($message);

        // Add message
        $this->addProcessedMessage($message);
    }

    protected function getHandlerInterface(): string
    {
        return CommandHandlerInterface::class;
    }

    protected function getMessageHandlersLimit(): int
    {
        // One command => one handler
        return 1;
    }

    /**
     * @param \BetaKiller\MessageBus\CommandMessageInterface $message
     * @param \BetaKiller\MessageBus\CommandHandlerInterface $handler
     */
    protected function processDelayedMessage($message, $handler): void
    {
        if (!$message->isAsync()) {
            throw new MessageBusException('Can not execute delayed sync command; make it async');
        }

        $this->process($message, $handler);
    }

    /**
     * @param \BetaKiller\MessageBus\CommandMessageInterface $message
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     * @return mixed|null
     */
    private function handle(CommandMessageInterface $message)
    {
        // Only one handler per message
        $handlerClassName = $this->getMessageHandlersClassNames($message)[0];

        $handler = $this->getHandlerInstance($handlerClassName);

        return $this->process($message, $handler);
    }

    /**
     * @param \BetaKiller\MessageBus\CommandMessageInterface $command
     * @param \BetaKiller\MessageBus\CommandHandlerInterface $handler
     *
     * @return mixed|null
     */
    private function process(CommandMessageInterface $command, CommandHandlerInterface $handler)
    {
        // Wrap every message bus processing with try-catch block and log exceptions
        try {
            $result = $handler->handleCommand($command);

            if ($result && $command->isAsync()) {
                throw new MessageBusException('Async command :name must not return result', [
                    ':name' => $this->getMessageName($command),
                ]);
            }

            return $result;
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);
        }

        return null;
    }
}
