<?php
namespace BetaKiller\IFace\Admin;

use BetaKiller\IFace\AbstractIFace;
use BetaKiller\Url\BeforeProcessingInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractAdminIFace extends AbstractIFace implements BeforeProcessingInterface
{
}
