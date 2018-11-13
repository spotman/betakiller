<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Model\SecondaryUrlElementModelTrait;
use BetaKiller\Url\ActionModelInterface;

class ActionPlainModel extends AbstractPlainEntityLinkedUrlElement implements ActionModelInterface
{
    use SecondaryUrlElementModelTrait;
}
