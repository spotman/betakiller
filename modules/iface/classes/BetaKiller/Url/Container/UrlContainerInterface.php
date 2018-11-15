<?php
declare(strict_types=1);

namespace BetaKiller\Url\Container;

use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;

interface UrlContainerInterface
{
    /**
     * @param \BetaKiller\Url\Parameter\UrlParameterInterface $object
     * @param bool|null                                       $ignoreDuplicate
     *
     * @return \BetaKiller\Url\Container\UrlContainerInterface
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
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param bool|null                                     $ignoreDuplicate
     *
     * @return \BetaKiller\Url\Container\UrlContainerInterface
     */
    public function setEntity(
        DispatchableEntityInterface $entity,
        ?bool $ignoreDuplicate = null
    ): UrlContainerInterface;

    /**
     * @param string $key
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface|mixed|null
     */
    public function getParameter(string $key);

    /**
     * @param string $className
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|mixed|null
     */
    public function getEntityByClassName(string $className);

    /**
     * @param string $className
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface|mixed|null
     * @throws \BetaKiller\Url\Container\UrlContainerException
     */
    public function getParameterByClassName(string $className);

    /**
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface[]
     */
    public function getAllParameters(): array;

    /**
     * Returns count of all parameters
     *
     * @return int
     */
    public function countParameters(): int;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasParameter(string $key): bool;

    /**
     * @param \BetaKiller\Url\Parameter\UrlParameterInterface $instance
     *
     * @return bool
     */
    public function hasParameterInstance(UrlParameterInterface $instance): bool;

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
     * @return \BetaKiller\Url\Container\UrlContainerInterface
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

    /**
     * @param \BetaKiller\Url\Container\UrlContainerInterface $from
     * @param bool|null                                       $overwrite
     */
    public function import(UrlContainerInterface $from, bool $overwrite = null): void;
}
