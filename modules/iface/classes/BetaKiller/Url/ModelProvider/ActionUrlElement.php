<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Model\HiddenInSitemapUrlElementModelTrait;
use BetaKiller\Url\ActionModelInterface;

final class ActionUrlElement extends AbstractPlainEntityLinkedUrlElement implements ActionModelInterface
{
    /**
     * @return string
     */
    public static function getXmlTagName(): string
    {
        return 'action';
    }
}
