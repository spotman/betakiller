<?php
declare(strict_types=1);

namespace BetaKiller\Task\Notification;

use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\NotificationException;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class TargetsFor extends AbstractTask
{
    private const OPTION_MESSAGE = 'message';

    /**
     * @var \BetaKiller\Notification\NotificationFacade
     */
    private $facade;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * TargetsFor constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade $facade
     * @param \Psr\Log\LoggerInterface                    $logger
     */
    public function __construct(NotificationFacade $facade, LoggerInterface $logger)
    {
        $this->facade = $facade;
        $this->logger = $logger;

        parent::__construct();
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
            self::OPTION_MESSAGE => null,
        ];
    }

    public function run(): void
    {
        $messageName = (string)$this->getOption(self::OPTION_MESSAGE, true);

        $group = $this->facade->getGroupByMessageCodename($messageName);

        $this->logger->info('Using group ":name"', [':name' => $group->getCodename()]);

        $roles = array_map(function (RoleInterface $role) {
            return $role->getName();
        }, $group->getRoles());

        $this->logger->info('Using role(s) ":names"', [
            ':names' => implode('", "', $roles),
        ]);

        $targets = $this->facade->getGroupTargets($group);

        $count = \count($targets);
        if ($count) {
            $this->logger->info('Some targets found (:count in total)', [':count' => $count]);
        } else {
            $this->logger->error('No targets found!');
        }

        foreach ($targets as $item) {
            if ($item instanceof UserInterface) {
                $this->logger->info(':name <:email> [:lang]', [
                    ':name'  => $item->getFullName(),
                    ':email' => $item->getEmail(),
                    ':lang'  => $item->getLanguageName(),
                ]);
            } else {
                throw new NotificationException('Unknown target type ":type"', [
                    ':type' => \gettype($item),
                ]);
            }
        }
    }
}
