<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\IFace\Url\UrlParameters;
use BetaKiller\IFace\Url\UrlParametersInterface;

interface IFaceInterface extends SeoMetaInterface
{
    /**
     * @return string
     */
    public function getCodename();

    /**
     * @return string
     */
    public function render();

    /**
     * @return string
     */
    public function getLayoutCodename();

    /**
     * Returns processed label
     *
     * @param UrlParametersInterface|null $params
     *
     * @return string
     */
    public function getLabel(UrlParametersInterface $params = null);

    /**
     * Returns label source/pattern
     *
     * @return string
     */
    public function getLabelSource();

    /**
     * Returns title source/pattern
     *
     * @return string
     */
    public function getTitleSource();

    /**
     * Returns description source/pattern
     *
     * @return string
     */
    public function getDescriptionSource();

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData();

    /**
     * @param \DateTime|NULL $last_modified
     *
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
     *
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
    public function getParent();

    /**
     * @param \BetaKiller\IFace\IFaceInterface $parent
     *
     * @return $this
     */
    public function setParent(IFaceInterface $parent);

    /**
     * Getter for current iface model
     *
     * @return IFaceModelInterface
     */
    public function getModel();

    /**
     * Setter for current iface model
     *
     * @param IFaceModelInterface $model
     *
     * @return $this
     */
    public function setModel(IFaceModelInterface $model);

    /**
     * @return bool
     */
    public function isDefault();

    /**
     * @return bool
     */
    public function isInStack();

    /**
     * @param \BetaKiller\IFace\Url\UrlParametersInterface|null $parameters
     *
     * @return bool
     */
    public function isCurrent(UrlParametersInterface $parameters = null);

    /**
     * @param \BetaKiller\IFace\Url\UrlParametersInterface|null $parameters
     * @param bool                                              $removeCyclingLinks
     * @param bool                                              $with_domain
     *
     * @return string
     */
    public function url(UrlParametersInterface $parameters = null, $removeCyclingLinks = true, $with_domain = true);

    /**
     * @return string
     */
    public function getUri();

    /**
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $params
     * @param int|null                            $limit
     * @param bool                                $withDomain
     *
     * @return string[]
     */
    public function getAvailableUrls(UrlParametersInterface $params, $limit = null, $withDomain = true);
}
