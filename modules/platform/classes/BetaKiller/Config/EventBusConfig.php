<?php
declare(strict_types=1);

namespace BetaKiller\Config;

use BetaKiller\MessageBus\EventBusConfigInterface;

final class EventBusConfig extends AbstractConfig implements EventBusConfigInterface
{
    protected function getConfigRootGroup(): string
    {
        return 'events';
    }

    /**
     * @throws \BetaKiller\Exception
     */
    public function getEventsMap(): array
    {
        return $this->getArray([]);
    }
}
