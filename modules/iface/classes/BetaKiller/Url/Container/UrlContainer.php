<?php
declare(strict_types=1);

namespace BetaKiller\Url\Container;

use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;
use BetaKiller\Utils\Registry\BasicRegistry;
use BetaKiller\Utils\Registry\RegistryException;

class UrlContainer implements UrlContainerInterface
{
    private $paramsRegistry;

    /**
     * Key => value pairs
     *
     * @var string[]
     */
    private $queryParts = [];

    /**
     * Array of query parts` keys
     *
     * @var string[]
     */
    private $usedQueryParts = [];

    /**
     * UrlContainer constructor.
     */
    public function __construct()
    {
        $this->paramsRegistry = new BasicRegistry;
    }

    /**
     * @return \BetaKiller\Url\Container\UrlContainerInterface
     */
    public static function create(): UrlContainerInterface
    {
        return new static;
    }

    /**
     * @param \BetaKiller\Url\Parameter\UrlParameterInterface $object
     * @param bool|null                                       $ignoreDuplicate
     *
     * @return \BetaKiller\Url\Container\UrlContainerInterface
     */
    public function setParameter(UrlParameterInterface $object, ?bool $ignoreDuplicate = null): UrlContainerInterface
    {
        $key = $object::getUrlContainerKey();

        try {
            $this->paramsRegistry->set($key, $object, $ignoreDuplicate);
        } catch (RegistryException $e) {
            throw UrlContainerException::wrap($e);
        }

        return $this;
    }

    /**
     * @param string $key
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|null
     * @throws \BetaKiller\Url\Container\UrlContainerException
     */
    public function getEntity(string $key): ?DispatchableEntityInterface
    {
        $param = $this->getParameter($key);

        if ($param && !($param instanceof DispatchableEntityInterface)) {
            throw new UrlContainerException('Parameter :key is not an entity', [':key' => $key]);
        }

        return $param;
    }

    /**
     * @param string $key
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface|null
     */
    public function getParameter(string $key): ?UrlParameterInterface
    {
        return $this->paramsRegistry->get($key);
    }

    /**
     * @param string|\BetaKiller\Model\DispatchableEntityInterface $className
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|mixed|null
     * @throws \BetaKiller\Url\Container\UrlContainerException
     */
    public function getEntityByClassName($className)
    {
        $key = $this->resolveObjectOrClassToKey($className, DispatchableEntityInterface::class);

        return $this->getEntity($key);
    }

    /**
     * @param string|\BetaKiller\Model\DispatchableEntityInterface $className
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|mixed|null
     * @throws \BetaKiller\Url\Container\UrlContainerException
     */
    public function getParameterByClassName($className)
    {
        $key = $this->resolveObjectOrClassToKey($className, UrlParameterInterface::class);

        return $this->getParameter($key);
    }

    /**
     * @param        $className
     *
     * @param string $targetClass
     *
     * @return string
     * @throws \BetaKiller\Url\Container\UrlContainerException
     */
    private function resolveObjectOrClassToKey($className, string $targetClass): string
    {
        if (\is_object($className)) {
            $className = \get_class($className);
        }

        if (!is_a($className, $targetClass, true)) {
            throw new UrlContainerException('Class :name must be instance of :must', [
                ':name' => $className,
                ':must' => $targetClass,
            ]);
        }

        /** @var \BetaKiller\Model\DispatchableEntityInterface $className Hack for autocomplete */
        return $className::getUrlContainerKey();
    }

    /**
     * @return \BetaKiller\Url\Container\UrlContainerInterface
     * @deprecated Url dispatching must be persistent
     */
    public function clear(): UrlContainerInterface
    {
        $this->paramsRegistry->clear();

        return $this;
    }

    /**
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface[]
     */
    public function getAllParameters(): array
    {
        return $this->paramsRegistry->getAll();
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasParameter(string $key): bool
    {
        return $this->paramsRegistry->has($key);
    }

    /**
     * @param \BetaKiller\Url\Parameter\UrlParameterInterface $instance
     *
     * @return bool
     */
    public function hasParameterInstance(UrlParameterInterface $instance): bool
    {
        return $this->hasParameter($instance::getUrlContainerKey());
    }

    public function isKey(UrlParameterInterface $param, string $key): bool
    {
        return $param::getUrlContainerKey() === $key;
    }

    /**
     * Returns keys of currently added items
     *
     * @return string[]
     */
    public function parametersKeys(): array
    {
        return $this->paramsRegistry->keys();
    }

    /**
     * Set query parts fetched from current HTTP request
     *
     * @param array $parts
     *
     * @return \BetaKiller\Url\Container\UrlContainerInterface
     */
    public function setQueryParts(array $parts): UrlContainerInterface
    {
        $this->queryParts     = $parts;
        $this->usedQueryParts = [];

        return $this;
    }

    /**
     * Returns query part value
     *
     * @param string    $key
     * @param bool|null $required
     *
     * @return string|string[]
     * @throws \BetaKiller\Url\Container\UrlContainerException
     */
    public function getQueryPart($key, $required = null)
    {
        if (isset($this->queryParts[$key])) {
            $this->usedQueryParts[] = $key;

            return $this->queryParts[$key];
        }

        if ($required) {
            throw new UrlContainerException('Missing [:key] query part', [':key' => $key]);
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getQueryPartsKeys(): array
    {
        return array_keys($this->queryParts);
    }

    /**
     * Returns true if HTTP request contains query parts which is never used in request processing
     *
     * @return array
     */
    public function getUnusedQueryPartsKeys(): array
    {
        return array_diff(array_keys($this->queryParts), $this->usedQueryParts);
    }
}
