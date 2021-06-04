<?php
namespace BetaKiller\Model;

abstract class AbstractOrmBasedMultipleParentsTreeModel extends \ORM implements MultipleParentsTreeModelInterface
{
    abstract protected function getTreeModelThroughTableName();

    protected const REL_PARENTS = 'parents';
    protected const REL_CHILDS = 'childs';

    protected function configure(): void
    {
        $this->has_many([
            self::REL_PARENTS => [
                'model'       => static::getModelName(),
                'foreign_key' => $this->getChildIdColumnName(),
                'far_key'     => $this->getParentIdColumnName(),
                'through'     => $this->getTreeModelThroughTableName(),
            ],

            self::REL_CHILDS => [
                'model'       => static::getModelName(),
                'foreign_key' => $this->getParentIdColumnName(),
                'far_key'     => $this->getChildIdColumnName(),
                'through'     => $this->getTreeModelThroughTableName(),
            ],
        ]);

        $this->load_with([
            self::REL_PARENTS,
        ]);
    }

    protected function getChildIdColumnName(): string
    {
        return 'child_id';
    }

    protected function getParentIdColumnName(): string
    {
        return 'parent_id';
    }

    /**
     * Return parents model or null
     *
     * @return $this[]
     * @throws \Kohana_Exception
     */
    public function getParents(): array
    {
        return $this->getAllRelated(self::REL_PARENTS);
    }

    /**
     * Return direct children
     *
     * @return \BetaKiller\Model\MultipleParentsTreeModelInterface[]
     */
    public function getChilds(): array
    {
        return $this->getAllRelated(self::REL_CHILDS);
    }

    /**
     * Return all parent models including in hierarchy
     *
     * @return \BetaKiller\Model\MultipleParentsTreeModelInterface[]
     */
    public function getAllParents(): array
    {
        return $this->getAllParentsRecursively($this);
    }

    /**
     * @inheritDoc
     */
    public function getAllChilds(): array
    {
        return $this->getAllChildsRecursively($this);
    }

    /**
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $child
     *
     * @return \BetaKiller\Model\MultipleParentsTreeModelInterface[]
     */
    protected function getAllParentsRecursively(MultipleParentsTreeModelInterface $child): array
    {
        $parents = [];

        foreach ($child->getParents() as $parent) {
            $parents[] = $parent;

            foreach ($this->getAllParentsRecursively($parent) as $grandParent) {
                $parents[] = $grandParent;
            }
        }

        return $parents;
    }

    /**
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $parent
     *
     * @return \BetaKiller\Model\MultipleParentsTreeModelInterface[]
     */
    protected function getAllChildsRecursively(MultipleParentsTreeModelInterface $parent): array
    {
        $childs = [];

        foreach ($parent->getChilds() as $child) {
            $childs[] = $child;

            foreach ($this->getAllChildsRecursively($child) as $grandChild) {
                $childs[] = $grandChild;
            }
        }

        return $childs;
    }

    /**
     * @return $this
     */
    protected function getParentsRelation(): self
    {
        return $this->get('parents');
    }

    /**
     * @return $this
     */
    protected function getChildsRelation(): self
    {
        return $this->get('childs');
    }

    /**
     * @param array|null $parentIDs
     *
     * @throws \Kohana_Exception
     */
    protected function filterParentIDs(array $parentIDs = null)
    {
        $parentsTableNameAlias = $this->table_name().'_parents';

        $this->join_related('parents', $parentsTableNameAlias);

        $parentIdCol = $parentsTableNameAlias.'.'.$this->getParentIdColumnName();

        if ($parentIDs) {
            $this->where($parentIdCol, 'IN', $parentIDs);
        } else {
            $this->where($parentIdCol, 'IS', null);
        }
    }

    /**
     * @param MultipleParentsTreeModelInterface $parent
     *
     * @return void
     */
    public function addParent(MultipleParentsTreeModelInterface $parent): void
    {
        $this->add('parents', $parent);
    }

    /**
     * @param MultipleParentsTreeModelInterface $parent
     *
     * @return void
     */
    public function removeParent(MultipleParentsTreeModelInterface $parent): void
    {
        $this->remove('parents', $parent);
    }

    /**
     * @param MultipleParentsTreeModelInterface $parent
     *
     * @return bool
     */
    public function hasParent(MultipleParentsTreeModelInterface $parent): bool
    {
        return $this->has('parents', $parent);
    }

    /**
     * @inheritDoc
     */
    public function isInherits(MultipleParentsTreeModelInterface $parent): bool
    {
        foreach ($this->getAllParentsRecursively($this) as $item) {
            if ($item->getID() === $parent->getID()) {
                return true;
            }
        }

        return false;
    }
}
