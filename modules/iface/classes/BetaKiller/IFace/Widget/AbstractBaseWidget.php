<?php
namespace BetaKiller\IFace\Widget;

use BetaKiller\Helper\IFaceHelperTrait;
use BetaKiller\IFace\Widget;
use Twig;

abstract class AbstractBaseWidget extends Widget\AbstractWidget
{
    use IFaceHelperTrait;

    /**
     * @Inject
     * @var \BetaKiller\Helper\IFaceHelper
     */
    protected $ifaceHelper;

    /**
     * Returns Twig view instance
     *
     * @param null  $file
     * @param array $data
     *
     * @return Twig
     */
    protected function view_factory($file = null, array $data = null)
    {
        return Twig::factory($file, $data);
    }
}
