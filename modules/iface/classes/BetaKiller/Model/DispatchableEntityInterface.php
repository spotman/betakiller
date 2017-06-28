<?php
namespace BetaKiller\Model;

use BetaKiller\IFace\Url\UrlParametersInterface;

interface DispatchableEntityInterface extends AbstractEntityInterface
{
    /**
     * Returns value of the $key property
     *
     * @param string $key
     *
     * @return string
     */
    public function getUrlKeyValue(string $key): string;

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
    public function presetLinkedEntities(UrlParametersInterface $parameters): void;

    /**
     * Returns key which will be used for storing model in UrlParameters registry.
     *
     * @return string
     */
    public static function getUrlParametersKey(): string;
}
