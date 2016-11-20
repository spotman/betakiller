<?php
namespace BetaKiller\Content\IFace;

use BetaKiller\IFace\IFace;

class ContentCategoryListing extends IFace
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
