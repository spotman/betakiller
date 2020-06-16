<?php
namespace BetaKiller\MessageBus;

abstract class AbstractMessageBus implements AbstractMessageBusInterface
{
    /**
     * @var callable[][]
     */
    private $bindings = [];

    /**
     * @return int
     */
    abstract protected function getMessageHandlersLimit(): int;

    /**
     * @param string   $messageClassName
     * @param callable $handler
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function on(string $messageClassName, callable $handler): void
    {
        $this->bindings[$messageClassName] = $this->bindings[$messageClassName] ?? [];

        $limit = $this->getMessageHandlersLimit();

        // Limit handlers count for CommandBus
        if ($limit && \count($this->bindings[$messageClassName]) > $limit) {
            throw new MessageBusException('Handlers limit exceed for :name message', [
                ':name' => $messageClassName,
            ]);
        }

        // Push handler
        $this->bindings[$messageClassName][] = $handler;
    }

    /**
     * @param \BetaKiller\MessageBus\MessageInterface $message
     *
     * @return callable[]
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    protected function getMessageHandlers(MessageInterface $message): array
    {
        $name = $this->getMessageName($message);

        $handlers = $this->bindings[$name] ?? [];

        if (!$handlers && $message instanceof MessageWithHandlersInterface) {
            throw new MessageBusException('No handlers found for :name event', [':name' => $name]);
        }

        return $handlers;
    }

    protected function getMessageName(MessageInterface $message): string
    {
        return \get_class($message);
    }
}
