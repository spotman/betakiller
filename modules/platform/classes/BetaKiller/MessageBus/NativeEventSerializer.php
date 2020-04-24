<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

final class NativeEventSerializer implements EventSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function encode(EventMessageInterface $event): string
    {
        return serialize($event);
    }

    /**
     * @inheritDoc
     */
    public function decode(string $data): EventMessageInterface
    {
        return \unserialize($data, [
            EventMessageInterface::class,
        ]);
    }
}
