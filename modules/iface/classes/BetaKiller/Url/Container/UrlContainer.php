<?php
declare(strict_types=1);

namespace BetaKiller\Url\Container;

use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Url\Parameter\CommonUrlParameterInterface;
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
    final public function __construct()
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
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param bool|null                                     $ignoreDuplicate
     *
     * @return \BetaKiller\Url\Container\UrlContainerInterface
     */
    public function setEntity(DispatchableEntityInterface $entity, ?bool $ignoreDuplicate = null): UrlContainerInterface
    {
        return $this->setParameter($entity, $ignoreDuplicate);
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
    public function getEntityByClassName(string $className)
    {
        return $this->findParameterByClassName($className, DispatchableEntityInterface::class);
    }

    /**
     * @param string|\BetaKiller\Model\DispatchableEntityInterface $className
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|mixed|null
     * @throws \BetaKiller\Url\Container\UrlContainerException
     */
    public function getParameterByClassName(string $className)
    {
        return $this->findParameterByClassName($className, UrlParameterInterface::class);
    }

    /**
     * @param string $className
     * @param string $targetInterface
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface|null
     * @throws \BetaKiller\Url\Container\UrlContainerException
     */
    private function findParameterByClassName(string $className, string $targetInterface): ?UrlParameterInterface
    {
        $found = null;

        foreach ($this->getAllParameters() as $parameter) {
            if (!$parameter instanceof $className) {
                continue;
            }

            if ($found) {
                throw new UrlContainerException('Multiple URL parameters are matching with :target', [
                    ':name' => $className,
                ]);
            }

            $found = $parameter;
        }

        if ($found && !$found instanceof $targetInterface) {
            throw new UrlContainerException('Class :name must be instance of :must', [
                ':name' => \get_class($found),
                ':must' => $targetInterface,
            ]);
        }

        return $found;
    }

//    /**
//     * @param        $className
//     *
//     * @param string $targetClass
//     *
//     * @return string
//     * @throws \BetaKiller\Url\Container\UrlContainerException
//     */
//    private function resolveObjectOrClassToKey($className, string $targetClass): string
//    {
//        if (\is_object($className)) {
//            $className = \get_class($className);
//        }
//
//        if (!is_a($className, $targetClass, true)) {
//            throw new UrlContainerException('Class :name must be instance of :must', [
//                ':name' => $className,
//                ':must' => $targetClass,
//            ]);
//        }
//
//        /** @var \BetaKiller\Model\DispatchableEntityInterface $className Hack for autocomplete */
//        return $className::getUrlContainerKey();
//    }

    /**
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface[]
     */
    public function getAllParameters(): array
    {
        return $this->paramsRegistry->getAll();
    }

    /**
     * Returns count of all parameters
     *
     * @return int
     */
    public function countParameters(): int
    {
        return $this->paramsRegistry->count();
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
     * @inheritDoc
     */
    public function hasParameterWithClassName(string $className): bool
    {
        return (bool)$this->findParameterByClassName($className, UrlParameterInterface::class);
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

    /**
     * @param \BetaKiller\Url\Parameter\UrlParameterInterface $param
     * @param string                                          $key
     *
     * @return bool
     */
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
     * @inheritDoc
     */
    public function getQueryPart(string $key, bool $required = null)
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

    /**
     * @param \BetaKiller\Url\Container\UrlContainerInterface $from
     * @param bool|null                                       $overwrite
     *
     * @return \BetaKiller\Url\Container\UrlContainerInterface
     */
    public function import(UrlContainerInterface $from, bool $overwrite = null): UrlContainerInterface
    {
        foreach ($from->getAllParameters() as $param) {
            // Skip existing params
            if (!$overwrite && $this->hasParameterInstance($param)) {
                continue;
            }

            // Skip common params like UTM markers
            if ($param instanceof CommonUrlParameterInterface) {
                continue;
            }

            $this->setParameter($param, true);
        }

        return $this;
    }
}
