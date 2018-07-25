<?php
namespace BetaKiller\Url;

interface UrlElementInterface
{
    public const URL_KEY = 'codename';

    /**
     * Returns codename
     *
     * @return string
     */
    public function getCodename(): string;

    /**
     * Returns parent element codename (if parent exists)
     *
     * @return null|string
     */
    public function getParentCodename(): ?string;

    /**
     * Returns element`s url part
     *
     * @return string
     */
    public function getUri(): string;

    /**
     * @param string $value
     */
    public function setUri(string $value): void;

    /**
     * Returns TRUE if current URL element is hidden in sitemap
     *
     * @return bool
     */
    public function isHiddenInSiteMap(): bool;

    /**
     * Returns array of additional ACL rules in format <ResourceName>.<permissionName> (eq, ["Admin.enabled"])
     *
     * @return string[]
     */
    public function getAdditionalAclRules(): array;
}
