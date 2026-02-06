<?php

declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Console\ConsoleTaskInterface;
use BetaKiller\MessageBus\BoundedEventMessageInterface;
use BetaKiller\MessageBus\BoundedEventTransportInterface;
use BetaKiller\MessageBus\OutboundEventMessageInterface;
use BetaKiller\MessageBus\OutboundEventTransportInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\Loop;

use function React\Promise\reject;

final readonly class EventBus implements ConsoleTaskInterface
{
    public function __construct(
        private BoundedEventTransportInterface $bounded,
        private OutboundEventTransportInterface $outbound,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            // No options here
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $loop = Loop::get();

        $this->outbound->subscribeAnyOutbound(function (OutboundEventMessageInterface $event) {
            $this->logger->info('<= :name :data', [
                ':name' => $event->getOutboundName(),
                ':data' => json_encode($event->getOutboundData()),
            ]);

            return reject();
        });

        $this->bounded->subscribeAnyBounded(function (BoundedEventMessageInterface $event) {
            $this->logger->info('[] :name', [
                ':name' => $event::getExternalName(),
            ]);

            return reject();
        });

        $this->outbound->startConsuming($loop);
        $this->bounded->startConsuming($loop);

        $this->logger->info('Listening to bounded and outbound events');

        $loop->run();
    }
}
