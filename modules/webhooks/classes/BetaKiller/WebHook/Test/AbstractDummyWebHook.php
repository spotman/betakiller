<?php
declare(strict_types=1);

namespace BetaKiller\WebHook\Test;

use BetaKiller\WebHook\AbstractWebHook;
use BetaKiller\WebHook\RequestDefinition;
use BetaKiller\WebHook\RequestDefinitionInterface;
use Worknector\Helper\DateTimeHelper;

abstract class AbstractDummyWebHook extends AbstractWebHook
{
    public const SERVICE_NAME = 'Test';

    public function getRequestDefinition(): RequestDefinitionInterface
    {
        $time = new \DateTimeImmutable('now', DateTimeHelper::getUtcTimezone());

        return RequestDefinition::create(
            'get',
            [
                'ID' => null,
                'EventType'   => $this->getModel()->getEventName(),
                'Date'        => $time->getTimestamp(),
            ]
        );
    }
}
