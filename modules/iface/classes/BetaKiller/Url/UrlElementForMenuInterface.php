<?php
declare(strict_types=1);

namespace BetaKiller\Url;

interface UrlElementForMenuInterface extends UrlElementWithLabelInterface
{
    public const OPTION_MENU = 'menu';

    /**
     * Returns menu codename to which URL is assigned
     *
     * @return null|string
     */
    public function getMenuName(): ?string;
}
