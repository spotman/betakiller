<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Model\HiddenInSitemapUrlElementModelTrait;
use BetaKiller\Url\ActionModelInterface;

class ActionPlainModel extends AbstractPlainEntityLinkedUrlElement implements ActionModelInterface
{
    use HiddenInSitemapUrlElementModelTrait;

    /**
     * @return string
     */
    public static function getXmlTagName(): string
    {
        return 'action';
    }
}
