<?php
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
     * @return array
     */
    public function getGroups(): array
    {
        $groups = $this->get(self::PATH_GROUPS);

        return array_keys($groups);
    }

    /**
     * @param string $groupCodename
     *
     * @return array
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
