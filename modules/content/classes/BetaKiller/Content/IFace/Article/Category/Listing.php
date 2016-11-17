<?php
namespace BetaKiller\Content\IFace\Article\Category;

use BetaKiller\IFace\IFace;

class Listing extends IFace
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        return [
            'categories' => [],
        ];
    }
}
