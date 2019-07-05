<?php
declare(strict_types=1);

namespace BetaKiller\Url;

interface DummyModelInterface extends UrlElementForMenuInterface, UrlElementWithLayoutInterface
{
    public const OPTION_REDIRECT = 'redirect';

    /**
     * Returns UrlElement codename (if defined)
     *
     * @return string|null
     */
    public function getRedirectTarget(): ?string;
}
