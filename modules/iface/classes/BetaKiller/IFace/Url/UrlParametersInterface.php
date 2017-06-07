<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\Model\DispatchableEntityInterface;

interface UrlParametersInterface
{
    /**
     * Creates new instance of
     *
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public static function create(): UrlParametersInterface;

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $object
     * @param bool|null                                     $ignoreDuplicate
     *
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function setEntity(DispatchableEntityInterface $object, ?bool $ignoreDuplicate = null): UrlParametersInterface;

    /**
     * @param string $key
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|mixed|null
     */
    public function getEntity(string $key);

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|string $className
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|mixed|null
     */
    public function getEntityByClassName($className);

    /**
     * @return \BetaKiller\Model\DispatchableEntityInterface[]
     */
    public function getAllEntities(): array;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasEntity(string $key): bool;

    /**
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     * @deprecated Url parameters must be persistent
     */
    public function clear(): UrlParametersInterface;

    /**
     * Returns keys of currently added Entity items
     *
     * @return string[]
     */
    public function entitiesKeys(): array;

    /**
     * Set query parts fetched from current HTTP request
     *
     * @param array $parts
     *
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function setQueryParts(array $parts): UrlParametersInterface;

    /**
     * Returns query part value
     *
     * @param string    $key
     * @param bool|null $required
     *
     * @return string|int|array
     */
    public function getQueryPart($key, $required = null);

    /**
     * Returns true if HTTP request contains query parts which is never used in request processing
     *
     * @return array
     */
    public function getUnusedQueryPartsKeys(): array;
}
