<?php
namespace BetaKiller\Model;

abstract class AbstractOrmBasedMultipleParentsTreeModel extends \ORM implements MultipleParentsTreeModelInterface
{
    abstract protected function getTreeModelThroughTableName(): string;

    public const REL_PARENTS  = 'parents';
    public const REL_CHILDREN = 'children';

    protected function configure(): void
    {
        $this->has_many([
            self::REL_PARENTS => [
                'model'       => static::getModelName(),
                'foreign_key' => static::getChildIdColumnName(),
                'far_key'     => static::getParentIdColumnName(),
                'through'     => $this->getTreeModelThroughTableName(),
            ],

            self::REL_CHILDREN => [
                'model'       => static::getModelName(),
                'foreign_key' => static::getParentIdColumnName(),
                'far_key'     => static::getChildIdColumnName(),
                'through'     => $this->getTreeModelThroughTableName(),
            ],
        ]);

//        $this->load_with([
//            self::REL_PARENTS,
//        ]);
    }

    public static function getChildIdColumnName(): string
    {
        return 'child_id';
    }

    public static function getParentIdColumnName(): string
    {
        return 'parent_id';
    }
}
