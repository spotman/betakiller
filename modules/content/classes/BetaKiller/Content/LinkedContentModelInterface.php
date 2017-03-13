<?php
namespace BetaKiller\Content;

use BetaKiller\Helper\HasPublicUrlInterface;
use BetaKiller\Helper\HasAdminUrlInterface;

interface LinkedContentModelInterface extends HasPublicUrlInterface, HasAdminUrlInterface, HasLabelInterface
{
}
