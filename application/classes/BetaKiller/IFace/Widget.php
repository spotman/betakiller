<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\Base;
use Twig;

abstract class Widget extends Core\Widget
{
    use Base;

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
