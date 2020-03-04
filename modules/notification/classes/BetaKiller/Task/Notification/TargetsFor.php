<?php
declare(strict_types=1);

namespace BetaKiller\Task\Notification;

use BetaKiller\Model\NotificationFrequencyInterface;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\NotificationException;
use BetaKiller\Notification\NotificationFacade;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Psr\Log\LoggerInterface;

final class TargetsFor extends AbstractTask
{
    private const OPTION_MESSAGE = 'message';
    private const OPTION_FREQ    = 'freq';

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
            self::OPTION_FREQ    => null,
        ];
    }

    public function run(): void
    {
        $messageName = (string)$this->getOption(self::OPTION_MESSAGE, true);
        $freqName    = (string)$this->getOption(self::OPTION_FREQ, false);

        $group = $this->facade->getGroupByMessageCodename($messageName);

        $this->logger->debug('Using group ":name"', [':name' => $group->getCodename()]);

        $roles = array_map(static function (RoleInterface $role) {
            return $role->getName();
        }, $group->getRoles());

        $this->logger->debug('Using role(s) ":names"', [
            ':names' => implode('", "', $roles),
        ]);

        $freq = $freqName ? $this->facade->getFrequencyByCodename($freqName) : null;

        if ($freq) {
            $this->logger->debug('Using frequency ":name"', [
                ':name' => $freqName,
            ]);
        }

        $targets = $this->facade->findGroupFreqTargets($group, $freq);

        $count = \count($targets);
        if ($count) {
            $this->logger->info('Some targets found (:count in total)', [':count' => $count]);
        } else {
            $this->logger->error('No targets found!');
        }

        foreach ($targets as $item) {
            $targetFreq = $this->getTargetGroupFrequencyName($group, $item);

            if ($item instanceof UserInterface) {
                $this->logger->info('[:lang] :email [:freq]', [
                    ':email' => $item->getEmail(),
                    ':lang'  => $item->getLanguageIsoCode(),
                    ':freq'  => $targetFreq ? $targetFreq->getCodename() : 'immediate',
                ]);
            } else {
                throw new NotificationException('Unknown target type ":type"', [
                    ':type' => \gettype($item),
                ]);
            }
        }
    }

    public function getTargetGroupFrequencyName(NotificationGroupInterface $group, MessageTargetInterface $target): ?NotificationFrequencyInterface
    {
        if (!$target instanceof UserInterface) {
            throw new \LogicException();
        }

        return $this->facade->getGroupFrequency($group, $target);
    }
}
