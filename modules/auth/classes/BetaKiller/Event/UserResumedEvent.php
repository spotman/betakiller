<?php
declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\MessageBus\MessageWithHandlersInterface;

final class UserResumedEvent extends AbstractUserWorkflowEvent implements MessageWithHandlersInterface
{
}
