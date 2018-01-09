<?php
namespace BetaKiller\IFace\Admin\Content\Shortcode;

use BetaKiller\IFace\Admin\AbstractAdminBase;

class Index extends AbstractAdminBase
{
    /**
     * @var
     */
    private $repo;

    /**
     * Returns data for View
     *
     * @return array
     */
    public function getData(): array
    {
        // TODO: List of custom tags maybe
        return [];
    }
}
