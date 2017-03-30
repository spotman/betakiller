<?php
namespace BetaKiller\IFace\Admin;

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
