<?php
declare(strict_types=1);

namespace BetaKiller\Task\Notification;

class ImportGroups extends ImportGroup
{
    public function run(): void
    {
        $continue = $this->read(
        /** @lang text */
            'Groups that are not in config will be disabled. Continue? [yes/no]'
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
        $this->deleteGroups();

        //
        foreach ($this->getGroupsCodenamesFromConfig() as $groupCodename) {
            $this->importGroup($groupCodename);
        }

        //
        $this->writeLog('Groups successfully imported!');
    }

    /**
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function deleteGroups(): void
    {
        $groupsModels = $this->findGroupsEnabled();
        if (!$groupsModels) {
            return;
        }

        $groupsCodenamesConfig = $this->getGroupsCodenamesFromConfig();
        foreach ($groupsModels as $groupModel) {
            if (!\in_array($groupModel->getCodename(), $groupsCodenamesConfig, true)) {
                $this->deleteGroup($groupModel);
            }
        }
    }
}
