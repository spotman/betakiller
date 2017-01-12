<?php
namespace BetaKiller\IFace\Admin;

use BetaKiller\IFace\Admin\AdminBase;

class Index extends AdminBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        return [];
    }
}
