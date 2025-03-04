<?php

declare(strict_types=1);

namespace BetaKiller\Task\Notification;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Console\ConsoleTaskInterface;
use BetaKiller\Model\NotificationFrequencyInterface;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\NotificationException;
use BetaKiller\Notification\NotificationFacade;
use Psr\Log\LoggerInterface;

final readonly class TargetsFor implements ConsoleTaskInterface
{
    private const OPTION_MESSAGE = 'message';
    private const OPTION_FREQ    = 'freq';

    /**
     * TargetsFor constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade $facade
     * @param \Psr\Log\LoggerInterface                    $logger
     */
    public function __construct(private NotificationFacade $facade, private LoggerInterface $logger)
    {
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @param \BetaKiller\Console\ConsoleOptionBuilderInterface $builder *
     *
     * @return array
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            $builder->string(self::OPTION_MESSAGE)->required(),
            $builder->string(self::OPTION_FREQ)->optional(),
        ];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $messageName = $params->getString(self::OPTION_MESSAGE);

        $freqName = $params->has(self::OPTION_FREQ)
            ? $params->getString(self::OPTION_FREQ)
            : null;

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

        $targets = $this->facade->getGroupTargets($group, $freq);

        $count = \count($targets);
        if ($count) {
            $this->logger->info('Some targets found (:count in total)', [':count' => $count]);
        } else {
            $this->logger->error('No targets found!');
        }

        foreach ($targets as $item) {
            $targetFreq = $this->getTargetGroupFrequencyName($group, $item);

            if (!$item instanceof UserInterface) {
                throw new NotificationException('Unknown target type ":type"', [
                    ':type' => \gettype($item),
                ]);
            }

            $this->logger->info('[:lang] :email [:freq]', [
                ':email' => $item->getMessageEmail(),
                ':lang'  => $item->getLanguageIsoCode(),
                ':freq'  => $targetFreq ? $targetFreq->getCodename() : 'immediate',
            ]);
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
