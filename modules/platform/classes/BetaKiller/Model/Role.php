<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class Role extends AbstractOrmBasedMultipleParentsTreeModel implements RoleInterface
{
    public const TABLE_NAME              = 'roles';
    public const COL_NAME        = 'name';
    public const COL_DESCRIPTION = 'description';

    public const INHERITANCE_TABLE_NAME = 'roles_inheritance';

    protected function getTreeModelThroughTableName(): string
    {
        return self::INHERITANCE_TABLE_NAME;
    }

    protected function configure(): void
    {
        $this->has_many([
            'users' => [
                'model'   => 'User',
                'through' => 'roles_users',
            ],
        ]);

        parent::configure();
    }


    public function rules(): array
    {
        return [
            self::COL_NAME => [
                ['not_empty'],
                ['min_length', [':value', 3]],
                ['max_length', [':value', 32]],
            ],
            self::COL_DESCRIPTION => [
                ['max_length', [':value', 255]],
            ],
        ];
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\RoleInterface
     */
    public function setName(string $value): RoleInterface
    {
        $value = trim($value);
        $this->set(self::COL_NAME, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->get(self::COL_NAME);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string)$this->get(self::COL_DESCRIPTION);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\RoleInterface
     */
    public function setDescription(string $value): RoleInterface
    {
        $value = trim($value);
        $this->set(self::COL_DESCRIPTION, $value);

        return $this;
    }

    /**
     * Returns the string identifier of the Role
     *
     * @return string
     */
    public function getRoleId(): string
    {
        return $this->getName();
    }
}
