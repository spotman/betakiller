<?php
declare(strict_types=1);

namespace BetaKiller\Repository;


use BetaKiller\Model\SingleParentTreeModelInterface;

interface SingleParentTreeRepositoryInterface extends RepositoryInterface
{
    /**
     * Returns list of child models
     *
     * @param \BetaKiller\Model\SingleParentTreeModelInterface|null $parent
     *
     * @return SingleParentTreeModelInterface[]
     */
    public function getChildren(SingleParentTreeModelInterface $parent): array;

    /**
     * @return SingleParentTreeModelInterface[]
     */
    public function getRoot(): array;

    /**
     * @param \BetaKiller\Model\SingleParentTreeModelInterface $parent
     *
     * @return SingleParentTreeModelInterface[]|int[]
     */
    public function getAllChildren(SingleParentTreeModelInterface $parent): array;

    /**
     * @param \BetaKiller\Model\SingleParentTreeModelInterface $current
     * @param \BetaKiller\Model\SingleParentTreeModelInterface $needle
     *
     * @return bool
     */
    public function hasInAscendingBranch(
        SingleParentTreeModelInterface $current,
        SingleParentTreeModelInterface $needle
    ): bool;
}
