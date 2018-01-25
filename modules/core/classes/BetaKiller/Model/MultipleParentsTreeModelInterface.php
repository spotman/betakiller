<?php
namespace BetaKiller\Model;

interface MultipleParentsTreeModelInterface
{
    /**
     * Return parents models
     *
     * @return \BetaKiller\Model\MultipleParentsTreeModelInterface[]|mixed
     */
    public function getParents(): array;

    /**
     * Return all parent models including in hierarchy
     *
     * @return \BetaKiller\Model\MultipleParentsTreeModelInterface[]|mixed
     */
    public function getAllParents(): array;

    /**
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $parent
     *
     * @return $this
     */
    public function addParent(MultipleParentsTreeModelInterface $parent);

    /**
     * @param \BetaKiller\Model\MultipleParentsTreeModelInterface $parent
     *
     * @return $this
     */
    public function removeParent(MultipleParentsTreeModelInterface $parent);
}
