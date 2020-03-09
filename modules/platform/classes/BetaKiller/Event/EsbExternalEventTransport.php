<?php
declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\MessageBus\BoundedEventMessageInterface;
use BetaKiller\MessageBus\ExternalEventTransportInterface;
use BetaKiller\MessageBus\OutboundEventMessageInterface;
use Interop\Queue\Context;

class EsbExternalEventTransport implements ExternalEventTransportInterface
{
    public const OUTBOUND_TOPIC_NAME    = 'events.outbound';
    public const BOUNDED_TOPIC_NAME     = 'events.bounded';
    public const PROPERTY_BOUNDED_NAME  = 'external_name';
    public const PROPERTY_OUTBOUND_NAME = 'internal_name';

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
    private $outboundTopic;

    /**
     * @var \Interop\Queue\Topic
     */
    private $boundedTopic;

    /**
     * EsbExternalEventTransport constructor.
     *
     * @param \Interop\Queue\Context $context
     */
    public function __construct(Context $context)
    {
        $this->context       = $context;
        $this->outboundTopic = $context->createTopic(self::OUTBOUND_TOPIC_NAME);
        $this->boundedTopic  = $context->createTopic(self::BOUNDED_TOPIC_NAME);
        $this->producer      = $context->createProducer();
    }

    public function emitBounded(BoundedEventMessageInterface $event): void
    {
        $msg = $this->context->createMessage(\serialize($event), [
            self::PROPERTY_BOUNDED_NAME => \get_class($event),
        ]);

        $this->producer->send($this->boundedTopic, $msg);
    }

    public function emitOutbound(OutboundEventMessageInterface $event): void
    {
        $msg = $this->context->createMessage(json_encode($event->getOutboundData()), [
            self::PROPERTY_OUTBOUND_NAME => $event->getOutboundName(),
        ]);

        $this->producer->send($this->outboundTopic, $msg);
    }
}
