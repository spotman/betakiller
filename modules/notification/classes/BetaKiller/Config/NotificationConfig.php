<?php
declare(strict_types=1);

namespace BetaKiller\Config;
 
class NotificationConfig extends AbstractConfig implements NotificationConfigInterface
{
    /**
     * @return string
     */
    protected function getConfigRootGroup(): string
    {
        return self::CONFIG_GROUP_NAME;
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
     * @return string[]
     * @throws \BetaKiller\Exception
     */
    public function getGroupMessages(string $groupCodename): array
    {
        $messages = [];

        foreach ((array)$this->get(self::PATH_MESSAGES) as $messageCodename => $messageConfig) {
            $messageGroup = $messageConfig['group'] ?? null;

            if ($messageGroup === $groupCodename) {
                $messages[] = $messageCodename;
            }
        }

        return $messages;
    }
}
