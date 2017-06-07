<?php
namespace BetaKiller\IFace\Widget;

use Twig;
use View;

abstract class AbstractBaseWidget extends AbstractWidget
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\IFaceHelper
     */
    protected $ifaceHelper;

    /**
     * @Inject
     * @var \BetaKiller\Helper\AclHelper
     */
    protected $aclHelper;

    /**
     * Returns Twig view instance
     *
     * @param null  $file
     * @param array $data
     *
     * @return Twig|View
     */
    protected function view_factory($file = null, ?array $data = null): View
    {
        return Twig::factory($file, $data);
    }
}
