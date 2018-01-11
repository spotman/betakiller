<?php
namespace BetaKiller\Url;

interface UrlContainerInterface
{
    /**
     * Creates new instance of
     *
     * @return \BetaKiller\Url\UrlContainerInterface
     */
    public static function create(): UrlContainerInterface;

    /**
     * @param \BetaKiller\Url\UrlParameterInterface $object
     * @param bool|null                             $ignoreDuplicate
     *
     * @return \BetaKiller\Url\UrlContainerInterface
     */
    public function setParameter(
        UrlParameterInterface $object,
        ?bool $ignoreDuplicate = null
    ): UrlContainerInterface;

    /**
     * @param string $key
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|mixed|null
     */
    public function getEntity(string $key);

    /**
     * @param string $key
     *
     * @return \BetaKiller\Url\UrlParameterInterface|mixed|null
     */
    public function getParameter(string $key);

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|string $className
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|mixed|null
     */
    public function getEntityByClassName($className);

    /**
     * @param string|\BetaKiller\Url\UrlParameterInterface $className
     *
     * @return \BetaKiller\Url\UrlParameterInterface|mixed|null
     * @throws \BetaKiller\IFace\Exception\UrlContainerException
     */
    public function getParameterByClassName($className);

    /**
     * @return \BetaKiller\Url\UrlParameterInterface[]
     */
    public function getAllParameters(): array;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasParameter(string $key): bool;

    /**
     * @param \BetaKiller\Url\UrlParameterInterface $instance
     *
     * @return bool
     */
    public function hasParameterInstance(UrlParameterInterface $instance): bool;

    /**
     * @return \BetaKiller\Url\UrlContainerInterface
     * @deprecated Url parameters must not be cleared (this is a hack for persistent DI instances)
     */
    public function clear(): UrlContainerInterface;

    /**
     * Returns keys of currently added Entity items
     *
     * @return string[]
     */
    public function parametersKeys(): array;

    /**
     * Set query parts fetched from current HTTP request
     *
     * @param array $parts
     *
     * @return \BetaKiller\Url\UrlContainerInterface
     */
    public function setQueryParts(array $parts): UrlContainerInterface;

    /**
     * Returns query part value
     *
     * @param string $key
     * @param bool|null $required
     *
     * @return string|int|array
     */
    public function getQueryPart($key, $required = null);

    /**
     * @return string[]
     */
    public function getQueryPartsKeys(): array;

    /**
     * Returns true if HTTP request contains query parts which is never used in request processing
     *
     * @return array
     */
    public function getUnusedQueryPartsKeys(): array;
}
