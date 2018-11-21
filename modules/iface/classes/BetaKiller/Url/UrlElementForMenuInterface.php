<?php
declare(strict_types=1);

namespace BetaKiller\Url;

interface UrlElementForMenuInterface extends UrlElementInterface
{
    /**
     * Returns menu codename to which URL is assigned
     *
     * @return null|string
     */
    public function getMenuName(): ?string;
}
