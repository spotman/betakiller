<?php
namespace BetaKiller\Status;

class AbstractStatusAclModelOrm extends \ORM implements StatusAclModelInterface
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->belongs_to([
            'role'  =>  [
                'model' => 'Role',
                'foreign_key' => 'role_id',
            ],
        ]);

        $this->load_with(['role']);

        parent::_initialize();
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
        /** @var \BetaKiller\Status\StatusAclModelInterface[] $records */
        $records = $this
            ->where('action', '=', $action)
            ->get_all();

        $roles = [];

        foreach ($records as $record) {
            $roles[] = $record->getRole()->get_name();
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
