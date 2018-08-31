<?php
declare(strict_types=1);

namespace BetaKiller\Task\Notification;

use BetaKiller\Model\NotificationGroupInterface;

class ImportGroup extends AbstractImportGroup
{
    public function run(): void
    {
        $continue = $this->read(
        /** @lang text */
            'Group that are not in config will be disabled. Continue? [yes/no]'
        );
        $continue = strtolower($continue);
        while (!\in_array($continue, ['yes', 'no'])) {
            $continue = $this->read('Type: yes/no');
            $continue = strtolower($continue);
        }
        if ($continue === 'no') {
            return;
        }

        //
        $groupCodename = $this->read('Type group codename');

        /**
         * Group will be deleted if it is in storage but it is not in config
         */
        if (!$this->hasGroupCodenameInConfig($groupCodename)) {
            $groupModel = $this->findGroup($groupCodename);
            if ($groupModel) {
                $this->deleteGroup($groupModel);
            }

            $this->write(
                sprintf('Group "%s" not found in config', $groupCodename),
                self::COLOR_RED
            );

            return;
        }

        //
        $this->importGroup($groupCodename);

        //
        $this->write('Group successfully imported!', self::COLOR_GREEN);
    }

    /**
     * @param string $groupCodename
     */
    protected function importGroup(string $groupCodename): void
    {
        $this->write('Exporting group: '.$groupCodename, self::COLOR_LIGHT_BLUE);

        $groupModel = $this->findGroup($groupCodename);
        if (!$groupModel) {
            $groupModel = $this->createGroup($groupCodename);
        }

        $this->deleteRoles($groupModel);

        $rolesCodenames = $this->getGroupRolesCodenamesFromConfig($groupCodename);
        foreach ($rolesCodenames as $roleCodename) {
            $roleModel   = $this->findRole($roleCodename);
            $roleEnabled = $groupModel->isEnabledForRole($roleModel);
            if (!$roleEnabled) {
                $this->write('Adding role: '.$roleCodename, self::COLOR_LIGHT_BLUE);
                $groupModel->enableForRole($roleModel);
            }
        }
    }

    /**
     * @param \BetaKiller\Model\NotificationGroupInterface $groupModel
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function deleteGroup(NotificationGroupInterface $groupModel): void
    {
        $this->write(
            'Disabling group: '.$groupModel->getCodename(),
            self::COLOR_LIGHT_BLUE
        );
        $this->disableGroup($groupModel);
    }

    /**
     * @param \BetaKiller\Model\NotificationGroupInterface $groupModel
     */
    protected function deleteRoles(NotificationGroupInterface $groupModel): void
    {
        $rolesModels = $groupModel->findRoles();
        if (!$rolesModels) {
            return;
        }

        $rolesCodenamesConfig = $this->getGroupRolesCodenamesFromConfig($groupModel->getCodename());
        foreach ($rolesModels as $roleModel) {
            if (!\in_array($roleModel->getName(), $rolesCodenamesConfig, true)) {
                $this->write(
                    'Deleting role: '.$roleModel->getName(),
                    self::COLOR_LIGHT_BLUE
                );
                $groupModel->disableForRole($roleModel);
            }
        }
    }
}
