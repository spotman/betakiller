<?php
namespace BetaKiller\Url;

use BetaKiller\Helper\SeoMetaInterface;

interface IFaceModelInterface extends UrlElementInterface, SeoMetaInterface
{
    /**
     * Returns layout codename or null if using parent layout
     *
     * @return string
     */
    public function getLayoutCodename(): ?string;
}
