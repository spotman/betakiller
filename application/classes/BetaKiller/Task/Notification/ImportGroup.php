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

            $this->writeLog('Group ":codename" not found in config', [':codename' => $groupCodename]);

            return;
        }

        //
        $this->importGroup($groupCodename);

        //
        $this->writeLog('Group successfully imported!');
    }

    /**
     * @param string $groupCodename
     */
    protected function importGroup(string $groupCodename): void
    {
        $this->writeLog('Exporting group: :codename', [':codename' => $groupCodename]);

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
                $this->writeLog('Adding role: :codename', [':codename' => $roleModel->getName()]);
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
        $this->writeLog('Disabling group: :codename', [':codename' => $groupModel->getCodename()]);
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
                $this->writeLog('Deleting role: :codename', [':codename' => $roleModel->getName()]);
                $groupModel->disableForRole($roleModel);
            }
        }
    }
}
