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
        $groups = $this->get(self::PATH_GROUPS);
        if (!$groups) return [];

        return array_keys($groups);
    }

    /**
     * @param string $groupCodename
     *
     * @return string[] ['roleCodename1','roleCodename2',..]
     */
    public function getGroupRoles($groupCodename): array
    {
        $path                  = self::PATH_GROUP_ROLES;
        $path['groupCodename'] = $groupCodename;

        return (array)$this->get($path);
    }

    /**
     * @param string $messageCodename
     *
     * @return string
     */
    public function getMessageGroup($messageCodename): string
    {
        $path                    = self::PATH_MESSAGE_GROUP;
        $path['messageCodename'] = $messageCodename;

        return (string)$this->get($path);
    }
}
