<?php
namespace BetaKiller\MessageBus;

/**
 * Interface BoundedEventMessageInterface
 * Message must be processed in the external message queue (workers, tasks, etc)
 *
 * @package BetaKiller\MessageBus
 */
interface BoundedEventMessageInterface extends ExternalEventMessageInterface
{
}
