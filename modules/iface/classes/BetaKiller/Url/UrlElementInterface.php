<?php
namespace BetaKiller\Url;

interface UrlElementInterface extends \JsonSerializable
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
     * Returns key-value pairs for "query param name" => "Url parameter binding"
     * Frontend-only keys can be ignored by setting "binding" value to "null"
     * Example: [ "u" => "User.id", "r" => "Role.codename", "x" => null ]
     *
     * @return array<string, string|null>
     */
    public function getQueryParams(): array;

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

    /**
     * @return bool
     */
    public function isAclBypassed(): bool;

    /**
     * @return bool
     */
    public function hasEnvironmentRestrictions(): bool;

    /**
     * @return string[]
     */
    public function getAllowedEnvironments(): array;
}
