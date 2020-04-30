<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use Interop\Queue\Consumer;
use Interop\Queue\Context;
use Interop\Queue\Producer;
use Interop\Queue\Topic;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\PromiseInterface;

final class EsbOutboundEventTransport implements OutboundEventTransportInterface
{
    public const OUTBOUND_TOPIC_NAME = 'events.outbound';

    /**
     * @var \Interop\Queue\Context
     */
    private Context $context;

    /**
     * @var \Interop\Queue\Topic
     */
    private Topic $topic;

    /**
     * @var \Interop\Queue\Producer
     */
    private Producer $producer;

    /**
     * @var \BetaKiller\MessageBus\EventSerializerInterface
     */
    private EventSerializerInterface $serializer;

    /**
     * @var callable
     */
    private $handler;

    /**
     * @var \React\EventLoop\TimerInterface|null
     */
    private ?TimerInterface $timer = null;

    /**
     * EsbOutboundEventTransport constructor.
     *
     * @param \Interop\Queue\Context                          $context
     * @param \BetaKiller\MessageBus\EventSerializerInterface $serializer
     */
    public function __construct(Context $context, EventSerializerInterface $serializer)
    {
        $this->context  = $context;
        $this->topic    = $context->createTopic(self::OUTBOUND_TOPIC_NAME);
        $this->producer = $context->createProducer();

        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function publishOutbound(OutboundEventMessageInterface $event): void
    {
        $msg = $this->context->createMessage($this->serializer->encode($event));

        $this->producer->send($this->topic, $msg);
    }

    /**
     * @inheritDoc
     */
    public function subscribeAnyOutbound(callable $handler): void
    {
        if ($this->handler) {
            throw new \LogicException('Outbound subscription handler is already defined');
        }

        $this->handler = $handler;
    }

    /**
     * @inheritDoc
     */
    public function startConsuming(LoopInterface $loop): void
    {
        if ($this->timer) {
            throw new \LogicException('Consuming outbound events is already started');
        }

        $consumer = $this->context->createConsumer($this->topic);

        // Start consuming
        $this->timer = $loop->addPeriodicTimer(0.2, function () use ($consumer) {
            $this->fetchEvent($consumer);
        });
    }

    /**
     * @inheritDoc
     */
    public function stopConsuming(LoopInterface $loop): void
    {
        if ($this->timer) {
            $loop->cancelTimer($this->timer);
        }

        $this->timer = null;
    }

    private function fetchEvent(Consumer $consumer): void
    {
        $message = $consumer->receiveNoWait();

        if (!$message) {
            return;
        }

        $event = $this->serializer->decode($message->getBody());

        if (!$event instanceof OutboundEventMessageInterface) {
            throw new \InvalidArgumentException(
                sprintf('Event %s must implement OutboundEventMessageInterface', \get_class($event))
            );
        }

        // Forward processing to the handler
        $result = \call_user_func($this->handler, $event);

        if (!$result instanceof PromiseInterface) {
            throw new \InvalidArgumentException(
                sprintf('Outbound event handler must implement %s', PromiseInterface::class)
            );
        }

        $result->then(
            static function () use ($message, $consumer) {
                $consumer->acknowledge($message);
            },
            static function () use ($message, $consumer) {
                $consumer->reject($message);
            }
        );
    }
}
