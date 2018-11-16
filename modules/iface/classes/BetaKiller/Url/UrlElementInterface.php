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
     * Returns TRUE if URL element is marked as "default"
     *
     * @return bool
     */
    public function isDefault(): bool;

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
     * Returns TRUE if URL element provides dynamic url mapping
     *
     * @return bool
     */
    public function hasDynamicUrl(): bool;

    /**
     * Returns TRUE if URL element provides tree-like url mapping
     *
     * @return bool
     */
    public function hasTreeBehaviour(): bool;

    /**
     * Returns TRUE if current URL element is hidden in sitemap
     *
     * @return bool
     */
    public function isHiddenInSiteMap(): bool;

    /**
     * Returns zone codename where this URL element is placed
     *
     * @return string
     */
    public function getZoneName(): string;

    /**
     * Returns array of additional ACL rules in format <ResourceName>.<permissionName> (eq, ["Admin.enabled"])
     *
     * @return string[]
     */
    public function getAdditionalAclRules(): array;
}
