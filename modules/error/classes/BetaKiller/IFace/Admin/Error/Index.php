<?php
namespace BetaKiller\IFace\Admin\Error;

class Index extends ErrorAdminBase
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
