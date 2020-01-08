<?php
declare(strict_types=1);

namespace BetaKiller\Api\Method\UserNotification;

use BetaKiller\Model\UserInterface;
use BetaKiller\Notification\NotificationFacade;
use Spotman\Api\ApiAccessViolationException;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class SetGroupFrequencyApiMethod extends AbstractApiMethod
{
    private const ARG_GROUP = 'group';
    private const ARG_FREQ  = 'frequency';

    /**
     * @var \BetaKiller\Notification\NotificationFacade
     */
    private $notification;

    /**
     * SetGroupFrequencyApiMethod constructor.
     *
     * @param \BetaKiller\Notification\NotificationFacade $notification
     */
    public function __construct(NotificationFacade $notification)
    {
        $this->notification = $notification;
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->string(self::ARG_GROUP)
            ->string(self::ARG_FREQ);
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $groupCodename = $arguments->getString(self::ARG_GROUP);
        $freqCodename  = $arguments->getString(self::ARG_FREQ);

        $group = $this->notification->getGroupByCodename($groupCodename);

        if (!$group->isAllowedToUser($user)) {
            throw new ApiAccessViolationException('User ":user" is trying to config notification group ":group"', [
                ':user'  => $user->getID(),
                ':group' => $group->getCodename(),
            ]);
        }

        $freq = $this->notification->getFrequencyByCodename($freqCodename);

        $this->notification->setGroupFrequency($group, $user, $freq);

        return null;
    }
}
