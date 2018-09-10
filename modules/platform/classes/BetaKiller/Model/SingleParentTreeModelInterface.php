<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface SingleParentTreeModelInterface extends AbstractEntityInterface
{
    /**
     * Return parent model or null
     *
     * @return SingleParentTreeModelInterface|mixed|static|null
     */
    public function getParent();

    /**
     * @param \BetaKiller\Model\SingleParentTreeModelInterface|null $parent
     */
    public function setParent(?SingleParentTreeModelInterface $parent);

    /**
     * @return SingleParentTreeModelInterface[]
     */
    public function getAllParents(): array;
}
