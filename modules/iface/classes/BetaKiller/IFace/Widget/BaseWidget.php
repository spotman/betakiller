<?php
namespace BetaKiller\IFace\Widget;

use BetaKiller\Helper;
use BetaKiller\IFace\Widget;
use Twig;

abstract class BaseWidget extends Widget\AbstractWidget
{
    use Helper\IFaceTrait;

    /**
     * Returns Twig view instance
     *
     * @param null $file
     * @param array $data
     * @return Twig
     */
    protected function view_factory($file = NULL, array $data = NULL)
    {
        return Twig::factory($file, $data);
    }
}
