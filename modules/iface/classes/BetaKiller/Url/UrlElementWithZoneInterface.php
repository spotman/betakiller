<?php
declare(strict_types=1);

namespace BetaKiller\Url;

interface UrlElementWithZoneInterface extends UrlElementInterface
{
    /**
     * Returns zone codename where this URL element is placed
     *
     * @return string
     */
    public function getZoneName(): string;
}
