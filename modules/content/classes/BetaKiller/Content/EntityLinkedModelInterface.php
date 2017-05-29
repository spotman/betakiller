<?php
namespace BetaKiller\Content;

use BetaKiller\Helper\HasPublicUrlInterface;
use BetaKiller\Helper\HasAdminUrlInterface;

interface EntityLinkedModelInterface extends HasPublicUrlInterface, HasAdminUrlInterface, HasLabelInterface
{
}
