<?php
namespace BetaKiller\Url;

use BetaKiller\Helper\SeoMetaInterface;

interface IFaceModelInterface extends EntityLinkedUrlElementInterface, SeoMetaInterface, UrlElementForMenuInterface
{
    /**
     * Returns layout codename or null if using parent layout
     *
     * @return string
     */
    public function getLayoutCodename(): ?string;
}
