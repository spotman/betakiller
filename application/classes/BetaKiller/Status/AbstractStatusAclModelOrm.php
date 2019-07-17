<?php
namespace BetaKiller\Workflow;

/**
 * Class AbstractStatusAclModelOrm
 *
 * @package BetaKiller\Workflow
 * @deprecated
 */
class AbstractStatusAclModelOrm extends \ORM implements StatusAclModelInterface
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function configure(): void
    {
        $this->belongs_to([
            'role'  =>  [
                'model' => 'Role',
                'foreign_key' => 'role_id',
            ],
        ]);

        $this->load_with(['role']);
    }

    /**
     * Returns array of role`s names
     *
     * @param string $action
     *
     * @return string[]
     */
    public function getActionAllowedRoles($action)
    {
        /** @var \BetaKiller\Workflow\StatusAclModelInterface[] $records */
        $records = $this
            ->where('action', '=', $action)
            ->get_all();

        $roles = [];

        foreach ($records as $record) {
            $roles[] = $record->getRole()->getName();
        }

        return $roles;
    }

    /**
     * @return \BetaKiller\Model\RoleInterface
     */
    public function getRole()
    {
        return $this->get('role');
    }
}
