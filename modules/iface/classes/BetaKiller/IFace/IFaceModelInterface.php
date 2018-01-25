<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\Model\HasLabelInterface;
use BetaKiller\Model\SingleParentTreeModelInterface;

interface IFaceModelInterface extends SingleParentTreeModelInterface, SeoMetaInterface, HasLabelInterface
{
    public const URL_KEY = 'codename';

    /**
     * Returns iface codename
     *
     * @return string
     */
    public function getCodename(): string;

    /**
     * Returns parent IFace codename (if parent exists)
     *
     * @return null|string
     */
    public function getParentCodename(): ?string;

    /**
     * Returns iface url part
     *
     * @return string
     */
    public function getUri(): string;

    /**
     * @param string $value
     */
    public function setUri(string $value): void;

    /**
     * Returns TRUE if iface is marked as "default"
     *
     * @return bool
     */
    public function isDefault(): bool;

    /**
     * Returns TRUE if iface provides dynamic url mapping
     *
     * @return bool
     */
    public function hasDynamicUrl(): bool;

    /**
     * Returns TRUE if iface provides tree-like url mapping
     *
     * @return bool
     */
    public function hasTreeBehaviour(): bool;

    /**
     * Returns array representation of the model data
     *
     * @return array
     */
    public function asArray(): array;

    /**
     * Returns layout codename or null if using parent layout
     *
     * @return string
     */
    public function getLayoutCodename(): ?string;

    /**
     * Returns TRUE if current IFace is hidden in sitemap
     *
     * @return bool
     */
    public function hideInSiteMap(): bool;

    /**
     * Returns model name of the linked entity
     *
     * @return string
     */
    public function getEntityModelName(): ?string;

    /**
     * Returns entity [primary] action, applied by this IFace
     * 
     * @return string
     */
    public function getEntityActionName(): ?string;

    /**
     * Returns zone codename where this IFace is placed
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
