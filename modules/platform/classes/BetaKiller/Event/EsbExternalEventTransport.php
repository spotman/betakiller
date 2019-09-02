<?php
declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\MessageBus\ExternalEventTransportInterface;
use BetaKiller\MessageBus\OutboundEventMessageInterface;
use Interop\Queue\Context;

class EsbExternalEventTransport implements ExternalEventTransportInterface
{
    public const TOPIC_NAME            = 'events';
    public const PROPERTY_MESSAGE_NAME = 'name';

    /**
     * @var \Interop\Queue\Context
     */
    private $context;

    /**
     * @var \Interop\Queue\Producer|null
     */
    private $producer;

    /**
     * @var \Interop\Queue\Topic
     */
    private $topic;

    /**
     * EsbExternalEventTransport constructor.
     *
     * @param \Interop\Queue\Context $context
     */
    public function __construct(Context $context)
    {
        $this->context  = $context;
        $this->topic    = $context->createTopic(self::TOPIC_NAME);
        $this->producer = $context->createProducer();
    }

    /**
     * @param \BetaKiller\MessageBus\OutboundEventMessageInterface $event
     *
     * @return void
     */
    public function emit(OutboundEventMessageInterface $event): void
    {
        $msg = $this->context->createMessage(json_encode($event), [
            self::PROPERTY_MESSAGE_NAME => $event->getExternalName(),
        ]);

        $this->producer->send($this->topic, $msg);
    }
}
