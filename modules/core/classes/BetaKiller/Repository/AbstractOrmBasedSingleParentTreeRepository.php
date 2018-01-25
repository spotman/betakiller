<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\SingleParentTreeModelInterface;

abstract class AbstractOrmBasedSingleParentTreeRepository extends AbstractOrmBasedDispatchableRepository
    implements SingleParentTreeRepositoryInterface
{
    /**
     * @return \Generator|\BetaKiller\Model\SingleParentTreeModelInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getFullTree(): \Generator
    {
        foreach ($this->getChildsRecursive() as $item) {
            yield $item;
        }
    }

    /**
     * @param \BetaKiller\Model\SingleParentTreeModelInterface|null $parent
     *
     * @return \Generator|\BetaKiller\Model\SingleParentTreeModelInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function getChildsRecursive(?SingleParentTreeModelInterface $parent = null): \Generator
    {
        $layer = $parent
            ? $this->getChildren($parent)
            : $this->getRoot();

        foreach ($layer as $item) {
            yield $item;

            foreach ($this->getChildsRecursive($item) as $child) {
                yield $child;
            }
        }
    }

    /**
     * @return \BetaKiller\Model\SingleParentTreeModelInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getRoot(): array
    {
        $orm = $this->getOrmInstance();

        $this->customFilterForTreeTraversing($orm);

        return $this
            ->filterParent($orm, null)
            ->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\SingleParentTreeModelInterface $parent
     *
     * @return SingleParentTreeModelInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getChildren(SingleParentTreeModelInterface $parent): array
    {
        $orm = $this->getOrmInstance();

        $this->customFilterForTreeTraversing($orm);

        return $this
            ->filterParent($orm, $parent)
            ->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\SingleParentTreeModelInterface $parent
     *
     * @return SingleParentTreeModelInterface[]|int[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getAllChildren(SingleParentTreeModelInterface $parent): array
    {
        $items = [];

        foreach ($this->getChildsRecursive($parent) as $item) {
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param \BetaKiller\Model\SingleParentTreeModelInterface $parent
     *
     * @return array
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getAllChildrenIDs(SingleParentTreeModelInterface $parent): array
    {
        $ids = [];

        // Collect all children
        foreach ($this->getAllChildren($parent) as $child) {
            $ids[] = $child->getID();
        }

        return $ids;
    }

    /**
     * @param \BetaKiller\Model\SingleParentTreeModelInterface $current
     * @param \BetaKiller\Model\SingleParentTreeModelInterface $needle
     *
     * @return bool
     */
    public function hasInAscendingBranch(
        SingleParentTreeModelInterface $current,
        SingleParentTreeModelInterface $needle
    ): bool {
        $pointer = $current;

        do {
            if ($pointer->getID() === $needle->getID()) {
                return true;
            }
            $pointer = $pointer->getParent();
        } while ($pointer);

        return false;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface                $orm
     * @param \BetaKiller\Model\SingleParentTreeModelInterface|null $parent
     *
     * @return \BetaKiller\Repository\AbstractOrmBasedSingleParentTreeRepository
     */
    protected function filterParent(ExtendedOrmInterface $orm, ?SingleParentTreeModelInterface $parent): self
    {
        $parentColumn = $orm->object_column($this->getParentIdColumnName());

        if ($parent) {
            $orm->where($parentColumn, '=', $parent->getID());
        } else {
            $orm->where($parentColumn, 'IS', null);
        }

        return $this;
    }

    /**
     * @return string
     */
    abstract protected function getParentIdColumnName(): string;

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     *
     * @return void
     */
    abstract protected function customFilterForTreeTraversing(ExtendedOrmInterface $orm): void;
}
