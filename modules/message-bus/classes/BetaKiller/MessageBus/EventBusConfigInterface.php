<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface EventBusConfigInterface
{
    /**
     * Returns "event class FQCN" => "array of event handlers` FQCN"
     *
     * @return string[][]
     */
    public function getEventsMap(): array;
}
