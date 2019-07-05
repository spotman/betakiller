<?php
declare(strict_types=1);

namespace BetaKiller\Url;

interface UrlElementWithLayoutInterface extends UrlElementInterface
{
    public const OPTION_LAYOUT = 'layout';

    /**
     * Returns layout codename or null if using parent layout
     *
     * @return string
     */
    public function getLayoutCodename(): ?string;
}
