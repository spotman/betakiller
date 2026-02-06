<?php

declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\MessageBus\OutboundEventMessageInterface;
use BetaKiller\MessageBus\RestrictedMessageInterface;

/**
 * Marker interface for ESB events which must be forwarded to Server-Sent Event emitter
 */
interface ServerSentEventInterface extends OutboundEventMessageInterface, RestrictedMessageInterface
{
}
