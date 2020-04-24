<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use BetaKiller\Helper\LoggerHelper;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

final class EsbBoundedEventTransport implements BoundedEventTransportInterface
{
    private const BOUNDED_TOPIC_NAME = 'events.bounded';
    private const MESSAGE_PROP_NAME  = 'name';

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
     * @var \BetaKiller\MessageBus\EventSerializerInterface
     */
    private EventSerializerInterface $serializer;

    /**
     * @var \React\EventLoop\TimerInterface|null
     */
    private ?TimerInterface $timer = null;

    /**
     * @var callback[]
     */
    private array $handlers = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * EsbBoundedEventTransport constructor.
     *
     * @param \Interop\Queue\Context                          $context
     * @param \BetaKiller\MessageBus\EventSerializerInterface $serializer
     * @param \Psr\Log\LoggerInterface                        $logger
     */
    public function __construct(Context $context, EventSerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->context  = $context;
        $this->topic    = $context->createTopic(self::BOUNDED_TOPIC_NAME);
        $this->producer = $context->createProducer();

        $this->serializer = $serializer;
        $this->logger     = $logger;
    }

    public function publishBounded(BoundedEventMessageInterface $event): void
    {
        $msg = $this->context->createMessage($this->serializer->encode($event), [
            self::MESSAGE_PROP_NAME => \get_class($event),
        ]);

        $this->producer->send($this->topic, $msg);
    }

    /**
     * @inheritDoc
     *
     * @param string   $eventClassName
     * @param callable $handler
     *
     * @noinspection OffsetOperationsInspection
     */
    public function subscribeBounded(string $eventClassName, callable $handler): void
    {
        if (!is_a($eventClassName, BoundedEventMessageInterface::class, true)) {
            throw new \LogicException(
                sprintf('Event type %s must implement %s', $eventClassName, BoundedEventMessageInterface::class)
            );
        }

        if (isset($this->handlers[$eventClassName])) {
            throw new \LogicException(
                sprintf('Duplicate handler for event %s', $eventClassName)
            );
        }

        $this->handlers[$eventClassName] = $handler;
    }

    /**
     * @inheritDoc
     */
    public function startConsuming(LoopInterface $loop): void
    {
        if ($this->timer) {
            throw new \LogicException('Consuming is already started');
        }

        $consumer = $this->context->createConsumer($this->topic);

        // Start consuming
        $this->timer = $loop->addPeriodicTimer(0.2, function () use ($consumer) {
            try {
                $message = $consumer->receiveNoWait();

                if ($message) {
                    $this->processMessage($message);
                }
            } catch (\Throwable $e) {
                LoggerHelper::logRawException($this->logger, $e);
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function stopConsuming(LoopInterface $loop): void
    {
        if (!$this->timer) {
            throw new \LogicException('Consuming is not started');
        }

        $loop->cancelTimer($this->timer);

        $this->timer = null;
    }

    private function processMessage(Message $message): void
    {
        $eventName = $message->getProperty(self::MESSAGE_PROP_NAME);

        $this->logger->debug('ESB: received event :name', [
            ':name' => $eventName,
        ]);

        // Skip events without handlers
        if (!isset($this->handlers[$eventName])) {
            $this->logger->debug('ESB: no handlers for event, skipping :name', [
                ':name' => $eventName,
            ]);

            return;
        }

        $event = $this->serializer->decode($message->getBody());

        if (!$event instanceof BoundedEventMessageInterface) {
            throw new \InvalidArgumentException('Event must implement BoundedEventMessageInterface');
        }

        $this->handlers[$eventName]($event);
    }
}
