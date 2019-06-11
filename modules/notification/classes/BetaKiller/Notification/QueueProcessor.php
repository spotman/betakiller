<?php
declare(strict_types=1);

namespace BetaKiller\Notification;

use BetaKiller\Exception;
use BetaKiller\Helper\LoggerHelperTrait;
use Enqueue\Dbal\DbalMessage;
use Enqueue\Redis\RedisMessage;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Psr\Log\LoggerInterface;
use Throwable;

class QueueProcessor implements Processor
{
    use LoggerHelperTrait;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Notification\NotificationFacade
     */
    private $notification;

    /**
     * @var \BetaKiller\Notification\MessageSerializer
     */
    private $serializer;

    /**
     * QueueProcessor constructor.
     *
     * @param \BetaKiller\Notification\MessageSerializer  $serializer
     * @param \BetaKiller\Notification\NotificationFacade $notification
     * @param \Psr\Log\LoggerInterface                    $logger
     */
    public function __construct(
        MessageSerializer $serializer,
        NotificationFacade $notification,
        LoggerInterface $logger
    ) {
        $this->logger       = $logger;
        $this->notification = $notification;
        $this->serializer   = $serializer;
    }

    /**
     * The method has to return either self::ACK, self::REJECT, self::REQUEUE string.
     *
     * The method also can return an object.
     * It must implement __toString method and the method must return one of the constants from above.
     *
     * @param Message $queueMessage
     * @param Context $context
     *
     * @return string|object with __toString method implemented
     * @see https://github.com/php-enqueue/enqueue-dev/issues/474#issuecomment-424134439
     */
    public function process(Message $queueMessage, Context $context)
    {
        if (!$queueMessage instanceof RedisMessage) {
            throw new Exception('Queue message must implement :must', [
                ':must' => DbalMessage::class,
            ]);
        }

        try {
            // Unserialize message
            $message = $this->serializer->unserialize($queueMessage->getBody());

            // Send through transports
            return $this->notification->send($message) ? self::ACK : self::REJECT;
        } catch (Throwable $e) {
            $this->logException($this->logger, $e);

//            $queueMessage->setRedeliverAfter(3600);
//            return self::REQUEUE;

            // Temp fix for failing tasks
            return self::REJECT;
        }
    }
}
