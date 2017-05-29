<?php
namespace BetaKiller\IFace\Url;

interface DispatchableEntityInterface
{
    /**
     * Defines default uri for index element (this used if root IFace has dynamic url behaviour)
     */
    const DEFAULT_URI = 'index';

    /**
     * @return string
     */
    public function getModelName();

    /**
     * Returns value of the $key property
     *
     * @param string $key
     *
     * @return string
     */
    public function getUrlKeyValue($key);

    /**
     *
     * This method allows inheritor to preset linked model in URL parameters
     * It is executed after successful url dispatching
     *
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $parameters
     *
     * @return void
     * @deprecated Implement dynamic linking based on scheme (relations between entities)
     */
    public function presetLinkedModels(UrlParametersInterface $parameters);

    /**
     * Returns string identifier of current DataSource item
     *
     * @return string
     */
    public function getUrlItemID();

    /**
     * Returns key which will be used for storing model in UrlParameters registry.
     *
     * @return string
     */
    public static function getUrlParameterKey();
}
