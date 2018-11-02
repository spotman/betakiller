<?php
namespace BetaKiller\Model;

interface MultipleParentsTreeModelInterface
{
    /**
     * Return parents models
     *
     * @return \BetaKiller\Model\MultipleParentsTreeModelInterface[]
     */
    public function getParents(): array;

    /**
     * Return all parent models including in hierarchy
     *
     * @return \BetaKiller\Model\MultipleParentsTreeModelInterface[]
     */
    public function getAllParents(): array;

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
