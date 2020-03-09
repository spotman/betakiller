<?php
declare(strict_types=1);

namespace BetaKiller\Config;

use BetaKiller\Exception;

class NotificationConfig extends AbstractConfig implements NotificationConfigInterface
{
    public const CONFIG_GROUP_NAME       = 'notifications';
    public const PATH_TRANSPORTS         = ['transports'];
    public const PATH_GROUPS             = ['groups'];
    public const PATH_GROUP_IS_SYSTEM    = ['groups', 'groupCodename' => '', 'is_system'];
    public const PATH_GROUP_FREQ_CTRL    = ['groups', 'groupCodename' => '', 'freq_control'];
    public const PATH_GROUP_ROLES        = ['groups', 'groupCodename' => '', 'roles'];
    public const PATH_MESSAGES           = ['messages'];
    public const PATH_MESSAGE_GROUP      = ['messages', 'messageCodename' => '', 'group'];
    public const PATH_MESSAGE_ACTION     = ['messages', 'messageCodename' => '', 'action'];
    public const PATH_MESSAGE_TRANSPORT  = ['messages', 'messageCodename' => '', 'transport'];
    public const PATH_MESSAGE_BROADCAST  = ['messages', 'messageCodename' => '', 'broadcast'];
    public const PATH_MESSAGE_CRITICAL   = ['messages', 'messageCodename' => '', 'critical'];
    public const PATH_MESSAGE_DISMISS_ON = ['messages', 'messageCodename' => '', 'dismiss_on'];

    /**
     * @return string
     */
    protected function getConfigRootGroup(): string
    {
        return self::CONFIG_GROUP_NAME;
    }

    /**
     * @return string[] ['transportOneCodename','transportTwoCodename',..]
     */
    public function getTransports(): array
    {
        return (array)$this->get(self::PATH_TRANSPORTS);
    }

    /**
     * @return string[] ['groupCodename1','groupCodename1',..]
     */
    public function getGroups(): array
    {
        $groups = (array)$this->get(self::PATH_GROUPS);

        return array_keys($groups);
    }

    /**
     * @param string $groupCodename
     *
     * @return string[] ['roleCodename1','roleCodename2',..]
     * @throws \BetaKiller\Exception
     */
    public function getGroupRoles(string $groupCodename): array
    {
        $path                  = self::PATH_GROUP_ROLES;
        $path['groupCodename'] = $groupCodename;

        return (array)$this->get($path);
    }

    /**
     * @param string $groupCodename
     *
     * @return bool
     */
    public function isSystemGroup(string $groupCodename): bool
    {
        $path                  = self::PATH_GROUP_IS_SYSTEM;
        $path['groupCodename'] = $groupCodename;

        return (bool)$this->get($path, true);
    }

    /**
     * @param string $messageCodename
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    public function getMessageGroup(string $messageCodename): string
    {
        $path                    = self::PATH_MESSAGE_GROUP;
        $path['messageCodename'] = $messageCodename;

        return (string)$this->get($path);
    }

    /**
     * @param string $groupCodename
     *
     * @return bool
     */
    public function isGroupFreqControlled(string $groupCodename): bool
    {
        $path                  = self::PATH_GROUP_FREQ_CTRL;
        $path['groupCodename'] = $groupCodename;

        return (bool)$this->get($path, true);
    }

    /**
     * @param string $groupCodename
     *
     * @return string[]
     * @throws \BetaKiller\Exception
     */
    public function getGroupMessages(string $groupCodename): array
    {
        $messages = [];

        foreach ((array)$this->get(self::PATH_MESSAGES) as $messageCodename => $messageConfig) {
            $messageGroup = $messageConfig['group'] ?? null;

            if (!$messageGroup) {
                throw new Exception('Missing "group" in ":name" notification message config', [
                    ':name' => $messageCodename,
                ]);
            }

            if ($messageGroup === $groupCodename) {
                $messages[] = $messageCodename;
            }
        }

        return $messages;
    }

    /**
     * @inheritDoc
     */
    public function getMessageAction(string $messageCodename): string
    {
        $path                    = self::PATH_MESSAGE_ACTION;
        $path['messageCodename'] = $messageCodename;

        return (string)$this->get($path, true);
    }

    /**
     * Returns transport codename
     *
     * @param string $messageCodename
     *
     * @return string
     */
    public function getMessageTransport(string $messageCodename): string
    {
        $path                    = self::PATH_MESSAGE_TRANSPORT;
        $path['messageCodename'] = $messageCodename;

        return (string)$this->get($path, false);
    }

    /**
     * @param string $messageCodename
     *
     * @return array
     */
    public function getMessageDismissOnEvents(string $messageCodename): array
    {
        $path                    = self::PATH_MESSAGE_DISMISS_ON;
        $path['messageCodename'] = $messageCodename;

        return (array)($this->get($path, true) ?? []);
    }

    /**
     * @param string $messageCodename
     *
     * @return bool
     */
    public function isMessageBroadcast(string $messageCodename): bool
    {
        $path                    = self::PATH_MESSAGE_BROADCAST;
        $path['messageCodename'] = $messageCodename;

        return (bool)$this->get($path, true);
    }

    /**
     * @inheritDoc
     */
    public function isMessageCritical(string $messageCodename): bool
    {
        $path                    = self::PATH_MESSAGE_CRITICAL;
        $path['messageCodename'] = $messageCodename;

        return (bool)$this->get($path, true);
    }
}
