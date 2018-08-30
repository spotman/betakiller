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

        $groupModel = $this->findGroup($groupCodename);
        if ($groupModel) {
            $this->write('Deleting group: '.$groupCodename, self::COLOR_LIGHT_BLUE);
            $groupModel->delete();
        }

        $this->createGroup($groupCodename);

        $this->write('Groups successfully updated!', self::COLOR_GREEN);
    }

    /**
     * @param string $groupCodename
     */
    protected function createGroup(string $groupCodename): void
    {
        $this->write('Creating group: '.$groupCodename, self::COLOR_LIGHT_BLUE);
        $groupModel     = $this->createGroupModel($groupCodename)->save();
        $rolesCodenames = $this->getGroupRolesCodenamesFromConfig($groupCodename);
        foreach ($rolesCodenames as $roleCodename) {
            $this->write('Adding role: '.$roleCodename, self::COLOR_LIGHT_BLUE);
            $roleModel = $this->findRole($roleCodename);
            $groupModel->enableForRole($roleModel);
        }
    }
}
