<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Model\HasLabelInterface;

interface UrlElementWithLabelInterface extends UrlElementInterface, HasLabelInterface
{
    public const OPTION_LABEL = 'label';
}
