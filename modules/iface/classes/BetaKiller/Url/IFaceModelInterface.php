<?php
namespace BetaKiller\Url;

use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\Model\HasLabelInterface;

interface IFaceModelInterface extends EntityLinkedUrlElementInterface, SeoMetaInterface, HasLabelInterface,
    UrlElementForMenuInterface
{
    /**
     * Returns layout codename or null if using parent layout
     *
     * @return string
     */
    public function getLayoutCodename(): ?string;
}
