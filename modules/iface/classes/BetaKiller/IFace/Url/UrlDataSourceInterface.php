<?php
namespace BetaKiller\IFace\Url;

interface UrlDataSourceInterface
{
    /**
     * Performs search for model item where the $key property value is equal to $value
     *
     * @param string                                       $key
     * @param string                                       $value
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $parameters
     *
     * @return UrlDataSourceInterface|null
     */
    public function findByUrlKey($key, $value, UrlParametersInterface $parameters);

    /**
     * Returns default uri for index element (this used if root IFace has dynamic url behaviour)
     *
     * @return string
     */
    public function getDefaultUrlValue();

    /**
     * Returns value of the $key property
     *
     * @param string $key
     *
     * @return string
     */
    public function getUrlKeyValue($key);

    /**
     * Returns list of available items (model records) by $key property
     *
     * @param string                                       $key
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $parameters
     * @param null                                         $limit
     *
     * @return UrlDataSourceInterface[]
     */
    public function getAvailableItemsByUrlKey($key, UrlParametersInterface $parameters, $limit = null);

    /**
     *
     * This method allows inheritor to preset linked model in URL parameters
     * It is executed after successful url dispatching
     *
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $parameters
     *
     * @return void
     */
    public function presetLinkedModels(UrlParametersInterface $parameters);

    /**
     * Returns custom key which may be used for storing model in UrlParameters registry.
     * Default policy applies if NULL returned.
     *
     * @return string|null
     */
    public function getCustomUrlParametersKey();

    /**
     * Returns string identifier of current DataSource item
     *
     * @return string
     */
    public function getUrlItemID();
}
