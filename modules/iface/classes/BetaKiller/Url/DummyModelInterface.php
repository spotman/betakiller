<?php
declare(strict_types=1);

namespace BetaKiller\Url;

interface DummyModelInterface extends EntityLinkedUrlElementInterface, UrlElementForMenuInterface,
    UrlElementWithLayoutInterface
{
    public const OPTION_REDIRECT = 'redirect';
    public const OPTION_FORWARD  = 'forward';

    /**
     * Returns UrlElement codename (if defined)
     *
     * @return string|null
     */
    public function getRedirectTarget(): ?string;

    /**
     * Returns UrlElement codename to proceed instead of current Dummy (if defined)
     *
     * @return string|null
     */
    public function getForwardTarget(): ?string;
}
