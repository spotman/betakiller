<?php
namespace BetaKiller\Model;

interface AclResourceInterface extends SingleParentTreeModelInterface
{
    /**
     * @return string
     */
    public function getCodename(): string;

    /**
     * @return null|string
     */
    public function getParentResourceCodename(): ?string;
}
