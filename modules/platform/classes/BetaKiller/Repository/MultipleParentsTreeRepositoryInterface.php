<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\MultipleParentsTreeModelInterface;

interface MultipleParentsTreeRepositoryInterface extends RepositoryInterface
{
    /**
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $child
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $parent
     *
     * @return void
     */
    public function addParent(MultipleParentsTreeModelInterface $child, MultipleParentsTreeModelInterface $parent): void;

    /**
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $child
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $parent
     *
     * @return void
     */
    public function removeParent(MultipleParentsTreeModelInterface $child, MultipleParentsTreeModelInterface $parent): void;

    /**
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $child
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $parent
     *
     * @return bool
     */
    public function hasParent(MultipleParentsTreeModelInterface $child, MultipleParentsTreeModelInterface $parent): bool;

    /**
     * Return direct parents
     *
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $entity
     *
     * @return \BetaKiller\Model\MultipleParentsTreeModelInterface[]
     */
    public function getParents(MultipleParentsTreeModelInterface $entity): array;

    /**
     * Return all parent including in hierarchy
     *
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $entity
     *
     * @return \BetaKiller\Model\MultipleParentsTreeModelInterface[]
     */
    public function getAllParents(MultipleParentsTreeModelInterface $entity): array;

    /**
     * Return direct children
     *
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $entity
     *
     * @return MultipleParentsTreeModelInterface[]
     */
    public function getChildren(MultipleParentsTreeModelInterface $entity): array;

    /**
     * Return all children in hierarchy
     *
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $entity
     *
     * @return MultipleParentsTreeModelInterface[]
     */
    public function getAllChildren(MultipleParentsTreeModelInterface $entity): array;

    /**
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $child
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $parent
     *
     * @return bool
     */
    public function isInherits(
        MultipleParentsTreeModelInterface $child,
        MultipleParentsTreeModelInterface $parent
    ): bool;
}
