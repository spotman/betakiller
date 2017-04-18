<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentInterface;

interface IFaceModelInterface extends TreeModelSingleParentInterface, SeoMetaInterface
{
    /**
     * Returns iface codename
     *
     * @return string
     */
    public function getCodename();

    /**
     * Returns iface url part
     *
     * @return string
     */
    public function getUri();

    /**
     * Returns TRUE if iface is marked as "default"
     *
     * @return bool
     */
    public function isDefault();

    /**
     * Returns TRUE if iface provides dynamic url mapping
     *
     * @return bool
     */
    public function hasDynamicUrl();

    /**
     * Returns TRUE if iface provides tree-like url mapping
     *
     * @return bool
     */
    public function hasTreeBehaviour();

    /**
     * Returns array representation of the model data
     *
     * @return array
     */
    public function asArray();

    /**
     * Returns iface layout object
     *
     * @return string
     */
    public function getLayoutCodename();

    /**
     * Returns label for using in breadcrumbs and etc
     *
     * @return string
     */
    public function getLabel();

    /**
     * Returns TRUE if current IFace is hidden in sitemap
     *
     * @return bool
     */
    public function hideInSiteMap();
}
