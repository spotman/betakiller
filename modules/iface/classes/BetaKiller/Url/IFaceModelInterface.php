<?php
namespace BetaKiller\Url;

use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\Model\HasLabelInterface;

interface IFaceModelInterface extends EntityLinkedUrlElementInterface, SeoMetaInterface, HasLabelInterface
{
    /**
     * Returns TRUE if URL element is marked as "default"
     *
     * @return bool
     */
    public function isDefault(): bool;

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
     * Returns layout codename or null if using parent layout
     *
     * @return string
     */
    public function getLayoutCodename(): ?string;

    /**
     * Returns zone codename where this URL element is placed
     *
     * @return string
     */
    public function getZoneName(): string;

    /**
     * Returns menu codename to which URL is assigned
     *
     * @return null|string
     */
    public function getMenuName(): ?string;
}
