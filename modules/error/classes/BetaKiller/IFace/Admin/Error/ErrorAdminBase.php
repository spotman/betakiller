<?php
namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\IFace\Admin\DeveloperOnly;
use BetaKiller\Helper\ErrorHelperTrait;

abstract class ErrorAdminBase extends DeveloperOnly
{
    use ErrorHelperTrait;
}
