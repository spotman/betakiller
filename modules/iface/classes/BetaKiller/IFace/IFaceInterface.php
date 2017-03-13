<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\SeoMetaInterface;
use URL_Parameters;

interface IFaceInterface extends SeoMetaInterface
{
    /**
     * @return string
     */
    public function get_codename();

    /**
     * @return string
     */
    public function render();

    /**
     * @return string
     */
    public function get_layout_codename();

    /**
     * Returns processed label
     *
     * @return string
     */
    public function get_label();

    /**
     * Returns label source/pattern
     *
     * @return string
     */
    public function get_label_source();

    /**
     * Returns title source/pattern
     *
     * @return string
     */
    public function get_title_source();

    /**
     * Returns description source/pattern
     *
     * @return string
     */
    public function get_description_source();

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data();

    /**
     * @param \DateTime|NULL $last_modified
     * @return $this
     */
    public function setLastModified(\DateTime $last_modified);

    /**
     * @return \DateTime
     */
    public function getLastModified();

    /**
     * @return \DateTime
     */
    public function getDefaultLastModified();

    /**
     * @return \DateInterval
     */
    public function getDefaultExpiresInterval();

    /**
     * @param \DateInterval|NULL $expires
     * @return $this
     */
    public function setExpiresInterval(\DateInterval $expires);

    /**
     * @return \DateInterval
     */
    public function getExpiresInterval();

    /**
     * @return \DateTime
     */
    public function getExpiresDateTime();

    /**
     * @return int
     */
    public function getExpiresSeconds();

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     */
    public function before();

    /**
     * This hook executed after real IFace processing only (on every request if IFace output was not cached)
     * Place here the code that needs to be executed only after real IFace processing (collect performance stat, etc)
     */
    public function after();

    /**
     * @return string
     */
    public function __toString();

    /**
     * @return IFaceInterface
     */
    public function get_parent();

    /**
     * @param \BetaKiller\IFace\IFaceInterface $parent
     *
     * @return $this
     */
    public function set_parent(IFaceInterface $parent);

    /**
     * Getter for current iface model
     *
     * @return IFaceModelInterface
     */
    public function get_model();

    /**
     * Setter for current iface model
     *
     * @param IFaceModelInterface $model
     * @return $this
     */
    public function set_model(IFaceModelInterface $model);

    /**
     * @return bool
     */
    public function is_default();

    /**
     * @return bool
     */
    public function is_in_stack();

    /**
     * @param \URL_Parameters|NULL $parameters
     *
     * @return bool
     */
    public function is_current(URL_Parameters $parameters = NULL);

    /**
     * @param \URL_Parameters|NULL  $parameters
     * @param bool                  $remove_cycling_links
     * @param bool                  $with_domain
     *
     * @return string
     */
    public function url(URL_Parameters $parameters = NULL, $remove_cycling_links = TRUE, $with_domain = TRUE);

    /**
     * @return string
     */
    public function get_uri();

    /**
     * Returns TRUE if trailing slash is needed in url
     *
     * @return bool
     */
    public function is_trailing_slash_enabled();
}
