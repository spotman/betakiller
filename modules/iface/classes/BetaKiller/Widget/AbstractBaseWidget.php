<?php
namespace BetaKiller\Widget;

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
}
