<?php
declare(strict_types=1);

namespace BetaKiller\Task\NotificationsGroups;

class UpdateAll extends UpdateOne
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

        foreach ($this->getGroupsCodenamesFromConfig() as $groupCodename) {
            $this->createGroup($groupCodename);
        }

        $this->write('Groups successfully updated!', self::COLOR_GREEN);
    }
}
