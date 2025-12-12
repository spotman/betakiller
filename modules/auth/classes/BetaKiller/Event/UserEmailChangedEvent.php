<?php
declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\MessageBus\MessageWithHandlersInterface;

final readonly class UserEmailChangedEvent extends AbstractUserWorkflowEvent implements MessageWithHandlersInterface
{
}
