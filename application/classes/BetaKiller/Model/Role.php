<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class Role extends AbstractOrmBasedMultipleParentsTreeModel implements RoleInterface
{
    public const TABLE_NAME              = 'roles';
    public const TABLE_FIELD_NAME        = 'name';
    public const TABLE_FIELD_DESCRIPTION = 'description';

    public const INHERITANCE_TABLE_NAME = 'roles_inheritance';

    protected function getTreeModelThroughTableName()
    {
        return self::INHERITANCE_TABLE_NAME;
    }

    public function rules(): array
    {
        return [
            self::TABLE_FIELD_NAME        => [
                ['not_empty'],
                ['min_length', [':value', 4]],
                ['max_length', [':value', 32]],
            ],
            self::TABLE_FIELD_DESCRIPTION => [
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
        $this->set(self::TABLE_FIELD_NAME, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->get(self::TABLE_FIELD_NAME);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string)$this->get(self::TABLE_FIELD_DESCRIPTION);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\RoleInterface
     */
    public function setDescription(string $value): RoleInterface
    {
        $value = trim($value);
        $this->set(self::TABLE_FIELD_DESCRIPTION, $value);

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
