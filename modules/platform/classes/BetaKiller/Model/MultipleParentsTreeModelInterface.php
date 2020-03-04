<?php
namespace BetaKiller\Model;

interface MultipleParentsTreeModelInterface
{
    /**
     * Return direct parents
     *
     * @return \BetaKiller\Model\MultipleParentsTreeModelInterface[]
     */
    public function getParents(): array;

    /**
     * Return all parent including in hierarchy
     *
     * @return \BetaKiller\Model\MultipleParentsTreeModelInterface[]
     */
    public function getAllParents(): array;

    /**
     * Return direct children
     *
     * @return \BetaKiller\Model\MultipleParentsTreeModelInterface[]
     */
    public function getChilds(): array;

    /**
     * Return all children in hierarchy
     *
     * @return \BetaKiller\Model\MultipleParentsTreeModelInterface[]
     */
    public function getAllChilds(): array;

    /**
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $parent
     *
     * @return void
     */
    public function addParent(MultipleParentsTreeModelInterface $parent): void;

    /**
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $parent
     *
     * @return void
     */
    public function removeParent(MultipleParentsTreeModelInterface $parent): void;

    /**
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $parent
     *
     * @return bool
     */
    public function hasParent(MultipleParentsTreeModelInterface $parent): bool;
}
