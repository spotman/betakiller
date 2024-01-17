<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Notification;

use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Minion_CLI;
use Psr\Log\LoggerInterface;

final class PurgeMessages extends AbstractTask
{
    private const QUEUE_PRIORITY = 'priority';
    private const QUEUE_REGULAR  = 'regular';
    private const QUEUE_ANY      = 'any';

    private NotificationFacade $notification;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * PurgeMessages constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade $notification
     * @param \Psr\Log\LoggerInterface                    $logger
     */
    public function __construct(NotificationFacade $notification, LoggerInterface $logger)
    {
        parent::__construct();

        $this->notification = $notification;
        $this->logger       = $logger;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [
            // No options here
        ];
    }

    public function run(): void
    {
        $name = Minion_CLI::read('Select queue', [
            Minion_CLI::color(self::QUEUE_PRIORITY, 'blue') => self::QUEUE_PRIORITY,
            Minion_CLI::color(self::QUEUE_REGULAR, 'green') => self::QUEUE_REGULAR,
            Minion_CLI::color(self::QUEUE_ANY, 'red')       => self::QUEUE_ANY,
        ]);

        $confirm = Minion_CLI::read(sprintf('All messages in %s queue will be deleted. Are you sure?', $name), [
            'yes',
            'no',
        ]);

        if ($confirm !== 'yes') {
            return;
        }

        switch ($name) {
            case self::QUEUE_PRIORITY;
                $this->purgePriorityQueue();
                break;

            case self::QUEUE_REGULAR;
                $this->purgeRegularQueue();
                break;

            case self::QUEUE_ANY;
                $this->purgePriorityQueue();
                $this->purgeRegularQueue();
                break;

            default:
                throw new TaskException('Unknown queue to purge ":name"', [
                    ':name' => $name,
                ]);
        }

        $this->logger->info('Done!');
    }

    private function purgePriorityQueue(): void
    {
        $this->logger->info('Purging priority queue...');
        $this->notification->purgePriorityQueue();
    }

    private function purgeRegularQueue(): void
    {
        $this->logger->info('Purging regular queue...');
        $this->notification->purgeRegularQueue();
    }
}
