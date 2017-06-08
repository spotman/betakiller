<?php
namespace BetaKiller\IFace\Admin;

class Index extends AbstractAdminBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}
