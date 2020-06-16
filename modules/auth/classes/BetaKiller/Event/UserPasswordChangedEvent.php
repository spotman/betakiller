<?php
declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\MessageBus\MessageWithHandlersInterface;

final class UserPasswordChangedEvent extends AbstractUserWorkflowEvent implements MessageWithHandlersInterface
{
}
