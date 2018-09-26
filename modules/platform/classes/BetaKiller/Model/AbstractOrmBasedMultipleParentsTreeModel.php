<?php
namespace BetaKiller\Model;

abstract class AbstractOrmBasedMultipleParentsTreeModel extends \ORM implements MultipleParentsTreeModelInterface
{
    abstract protected function getTreeModelThroughTableName();

    protected function configure(): void
    {
        $this->has_many([
            'parents' => [
                'model'       => $this->getModelName(),
                'foreign_key' => $this->getChildIdColumnName(),
                'far_key'     => $this->getParentIdColumnName(),
                'through'     => $this->getTreeModelThroughTableName(),
            ],
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
        return $this->getParentsRelation()->find_all()->as_array();
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
     * @return $this
     */
    protected function getParentsRelation()
    {
        return $this->get('parents');
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
}
