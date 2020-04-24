<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface EventSerializerInterface
{
    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $event
     *
     * @return string
     */
    public function encode(EventMessageInterface $event): string;

    /**
     * @param string $data
     *
     * @return \BetaKiller\MessageBus\EventMessageInterface
     */
    public function decode(string $data): EventMessageInterface;
}
