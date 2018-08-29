<?php
declare(strict_types=1);

namespace BetaKiller\Task\NotificationsGroups;

class UpdateOne extends AbstractUpdate
{
    public function run(): void
    {
        $continue = $this->read(
        /** @lang text */
            'Delete group and create new from config? [yes/no]'
        );
        $continue = strtolower($continue);
        while (!\in_array($continue, ['yes', 'no'])) {
            $continue = $this->read('Type: yes/no');
            $continue = strtolower($continue);
        }
        if ($continue === 'no') {
            return;
        }

        $groupCodename = $this->read('Type group codename');
        if (!$this->hasGroupCodenameInConfig($groupCodename)) {
            $this->write(
                sprintf('Group "%s" not found in config', $groupCodename),
                self::COLOR_RED
            );

            return;
        }

        $this->write('Deleting group: '.$groupCodename, self::COLOR_LIGHT_BLUE);
        $this->deleteGroup($groupCodename);

        $this->write('Creating group: '.$groupCodename, self::COLOR_LIGHT_BLUE);
        $groupId        = $this->createGroup($groupCodename);
        $rolesCodenames = $this->getGroupRolesFromConfig($groupCodename);
        foreach ($rolesCodenames as $roleCodename) {
            $roleId = $this->getRoleId($roleCodename);
            $this->write('Adding role of group: '.$roleCodename, self::COLOR_LIGHT_BLUE);
            $this->addGroupRole($groupId, $roleId);
        }

        $this->write('Groups successfully updated!', self::COLOR_GREEN);
    }
}
