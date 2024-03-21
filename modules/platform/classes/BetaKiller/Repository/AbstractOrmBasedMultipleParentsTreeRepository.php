<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\AbstractOrmBasedMultipleParentsTreeModel;
use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\MultipleParentsTreeModelInterface;

abstract class AbstractOrmBasedMultipleParentsTreeRepository extends AbstractOrmBasedDispatchableRepository
    implements MultipleParentsTreeRepositoryInterface
{
    /**
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $child
     * @param MultipleParentsTreeModelInterface                   $parent
     *
     * @return void
     */
    public function addParent(MultipleParentsTreeModelInterface $child, MultipleParentsTreeModelInterface $parent): void
    {
        $child->add(AbstractOrmBasedMultipleParentsTreeModel::REL_PARENTS, $parent);
    }

    /**
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $child
     * @param MultipleParentsTreeModelInterface                   $parent
     *
     * @return void
     */
    public function removeParent(
        MultipleParentsTreeModelInterface $child,
        MultipleParentsTreeModelInterface $parent
    ): void {
        $child->remove(AbstractOrmBasedMultipleParentsTreeModel::REL_PARENTS, $parent);
    }

    /**
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $child
     * @param MultipleParentsTreeModelInterface                   $parent
     *
     * @return bool
     */
    public function hasParent(MultipleParentsTreeModelInterface $child, MultipleParentsTreeModelInterface $parent): bool
    {
        return $child->has(AbstractOrmBasedMultipleParentsTreeModel::REL_PARENTS, $parent);
    }

    public function getParents(MultipleParentsTreeModelInterface $entity): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterChild($orm, $entity)
            ->findAll($orm);
    }

    public function getAllParents(MultipleParentsTreeModelInterface $entity): array
    {
        $parents = [];

        foreach ($this->getParents($entity) as $parent) {
            $parents[] = $parent;

            foreach ($this->getAllParents($parent) as $grandParent) {
                $parents[] = $grandParent;
            }
        }

        return $parents;
    }

    public function getChildren(MultipleParentsTreeModelInterface $entity): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterParent($orm, $entity)
            ->findAll($orm);
    }

    public function getAllChildren(MultipleParentsTreeModelInterface $entity): array
    {
        $children = [];

        foreach ($this->getChildren($entity) as $child) {
            $children[] = $child;

            foreach ($this->getAllChildren($child) as $grandChild) {
                $children[] = $grandChild;
            }
        }

        return $children;
    }

    public function isInherits(
        MultipleParentsTreeModelInterface $child,
        MultipleParentsTreeModelInterface $parent
    ): bool {
        foreach ($this->getAllParents($child) as $item) {
            if ($item->getID() === $parent->getID()) {
                return true;
            }
        }

        return false;
    }

    protected function filterParent(ExtendedOrmInterface $orm, MultipleParentsTreeModelInterface $parent): self
    {
        $orm->filter_related(AbstractOrmBasedMultipleParentsTreeModel::REL_PARENTS, $parent);

        return $this;
    }

    protected function filterChild(ExtendedOrmInterface $orm, MultipleParentsTreeModelInterface $child): self
    {
        $orm->filter_related(AbstractOrmBasedMultipleParentsTreeModel::REL_CHILDREN, $child);

        return $this;
    }
}
