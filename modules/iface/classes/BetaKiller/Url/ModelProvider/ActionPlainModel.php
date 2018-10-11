<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Model\SecondaryUrlElementModelTrait;
use BetaKiller\Url\ActionModelInterface;

class ActionPlainModel extends AbstractPlainUrlElementModel implements ActionModelInterface
{
    use SecondaryUrlElementModelTrait;
}
