<?php
declare(strict_types=1);

namespace BetaKiller\Task\NotificationsGroups;

class UpdateAll extends AbstractUpdate
{
    public function run(): void
    {
        $continue = $this->read(
        /** @lang text */
            'Delete all groups and create new from ceonfig? [yes/no]'
        );
        $continue = strtolower($continue);
        while (!\in_array($continue, ['yes', 'no'])) {
            $continue = $this->read('Type: yes/no');
            $continue = strtolower($continue);
        }
        if ($continue === 'no') {
            return;
        }

        $this->write('Deleting groups', self::COLOR_LIGHT_BLUE);
        $this->deleteGroups();

        foreach ($this->getGroupsFromConfig() as $groupCodename) {
            $this->write('Creating group: '.$groupCodename, self::COLOR_LIGHT_BLUE);
            $groupId        = $this->createGroup($groupCodename);
            $rolesCodenames = $this->getGroupRolesFromConfig($groupCodename);
            foreach ($rolesCodenames as $roleCodename) {
                $roleId = $this->getRoleId($roleCodename);
                $this->write('Adding role of group: '.$roleCodename, self::COLOR_LIGHT_BLUE);
                $this->addGroupRole($groupId, $roleId);
            }
        }

        $this->write('Groups successfully updated!', self::COLOR_GREEN);
    }
}
