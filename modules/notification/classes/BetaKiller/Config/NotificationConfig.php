<?php

declare(strict_types=1);

namespace BetaKiller\Config;

use BetaKiller\Exception;

class NotificationConfig extends AbstractConfig implements NotificationConfigInterface
{
    /**
     * Root keys below
     */
    public const ROOT_TRANSPORTS = 'transports';
    public const ROOT_GROUPS     = 'groups';
    public const ROOT_MESSAGES   = 'messages';
    public const ROOT_UTM        = 'utm';

    /**
     * Group keys below
     */
    public const ROLES        = 'roles';
    public const IS_SYSTEM    = 'is_system';
    public const FREQ_CONTROL = 'freq_control';

    /**
     * Message keys below
     */
    public const GROUP      = 'group';
    public const ACTION     = 'action';
    public const TRANSPORT  = 'transport';
    public const BROADCAST  = 'broadcast';
    public const CRITICAL   = 'critical';
    public const DISMISS_ON = 'dismiss_on';

    private const CONFIG_GROUP_NAME       = 'notifications';
    private const PATH_TRANSPORTS         = [self::ROOT_TRANSPORTS];
    private const PATH_GROUPS             = [self::ROOT_GROUPS];
    private const PATH_GROUP_IS_SYSTEM    = [self::ROOT_GROUPS, 'groupCodename' => '', self::IS_SYSTEM];
    private const PATH_GROUP_FREQ_CTRL    = [self::ROOT_GROUPS, 'groupCodename' => '', self::FREQ_CONTROL];
    private const PATH_GROUP_ROLES        = [self::ROOT_GROUPS, 'groupCodename' => '', self::ROLES];
    private const PATH_MESSAGES           = [self::ROOT_MESSAGES];
    private const PATH_MESSAGE_GROUP      = [self::ROOT_MESSAGES, 'messageCodename' => '', self::GROUP];
    private const PATH_MESSAGE_ACTION     = [self::ROOT_MESSAGES, 'messageCodename' => '', self::ACTION];
    private const PATH_MESSAGE_TRANSPORT  = [self::ROOT_MESSAGES, 'messageCodename' => '', self::TRANSPORT];
    private const PATH_MESSAGE_BROADCAST  = [self::ROOT_MESSAGES, 'messageCodename' => '', self::BROADCAST];
    private const PATH_MESSAGE_CRITICAL   = [self::ROOT_MESSAGES, 'messageCodename' => '', self::CRITICAL];
    private const PATH_MESSAGE_DISMISS_ON = [self::ROOT_MESSAGES, 'messageCodename' => '', self::DISMISS_ON];

    private const PATH_UTM_TRANSPORT = [self::ROOT_UTM, 'transportCodename' => ''];

    /**
     * @return string
     */
    protected function getConfigRootGroup(): string
    {
        return self::CONFIG_GROUP_NAME;
    }

    /**
     * @inheritDoc
     */
    public function getTransports(): array
    {
        return (array)$this->get(self::PATH_TRANSPORTS);
    }

    /**
     * @inheritDoc
     */
    public function getGroups(): array
    {
        $groups = (array)$this->get(self::PATH_GROUPS);

        return array_keys($groups);
    }

    /**
     * @inheritDoc
     */
    public function getGroupRoles(string $groupCodename): array
    {
        $path                  = self::PATH_GROUP_ROLES;
        $path['groupCodename'] = $groupCodename;

        return (array)$this->get($path);
    }

    /**
     * @inheritDoc
     */
    public function isSystemGroup(string $groupCodename): bool
    {
        $path                  = self::PATH_GROUP_IS_SYSTEM;
        $path['groupCodename'] = $groupCodename;

        return (bool)$this->get($path, true);
    }

    /**
     * @inheritDoc
     */
    public function getMessageGroup(string $messageCodename): string
    {
        $path                    = self::PATH_MESSAGE_GROUP;
        $path['messageCodename'] = $messageCodename;

        return (string)$this->get($path);
    }

    /**
     * @inheritDoc
     */
    public function isGroupFreqControlled(string $groupCodename): bool
    {
        $path                  = self::PATH_GROUP_FREQ_CTRL;
        $path['groupCodename'] = $groupCodename;

        return (bool)$this->get($path, true);
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getMessageTransport(string $messageCodename): string
    {
        $path                    = self::PATH_MESSAGE_TRANSPORT;
        $path['messageCodename'] = $messageCodename;

        return (string)$this->get($path, false);
    }

    /**
     * @inheritDoc
     */
    public function getMessageDismissOnEvents(string $messageCodename): array
    {
        $path                    = self::PATH_MESSAGE_DISMISS_ON;
        $path['messageCodename'] = $messageCodename;

        return (array)($this->get($path, true) ?? []);
    }

    /**
     * @inheritDoc
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

    /**
     * @inheritDoc
     */
    public function getUtmMarkers(string $transportCodename): array
    {
        $path                      = self::PATH_UTM_TRANSPORT;
        $path['transportCodename'] = $transportCodename;

        return (array)$this->get($path, true);
    }
}
