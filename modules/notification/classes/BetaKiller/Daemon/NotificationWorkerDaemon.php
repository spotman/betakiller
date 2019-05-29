<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Notification\QueueProcessor;
use Enqueue\Consumption\ChainExtension;
use Enqueue\Consumption\Extension\LogExtension;
use Enqueue\Consumption\QueueConsumer;
use Interop\Queue\Context;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;

class NotificationWorkerDaemon implements DaemonInterface
{
    public const CODENAME = 'NotificationWorker';

    /**
     * @var \Interop\Queue\Context
     */
    private $context;

    /**
     * @var \BetaKiller\Notification\QueueProcessor
     */
    private $processor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * NotificationWorkerDaemon constructor.
     *
     * @param \Interop\Queue\Context                  $context
     * @param \BetaKiller\Notification\QueueProcessor $processor
     * @param \Psr\Log\LoggerInterface                $logger
     */
    public function __construct(Context $context, QueueProcessor $processor, LoggerInterface $logger)
    {
        $this->context   = $context;
        $this->processor = $processor;
        $this->logger    = $logger;
    }

    public function start(LoopInterface $loop): void
    {
        $extensions = new ChainExtension([
//            new SignalExtension(),
            new LogExtension(),
        ]);

        $consumer = new QueueConsumer($this->context, $extensions, [], $this->logger);

        $consumer->bind(NotificationFacade::QUEUE_NAME, $this->processor);

        $consumer->consume();
    }

    public function stop(): void
    {
        $this->context->close();
    }
}
