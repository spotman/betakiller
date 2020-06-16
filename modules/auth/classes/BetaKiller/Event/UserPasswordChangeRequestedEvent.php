<?php
declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\MessageBus\MessageWithHandlersInterface;

final class UserPasswordChangeRequestedEvent extends AbstractUserWorkflowEvent implements MessageWithHandlersInterface
{
}
