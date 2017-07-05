<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\IFace\Exception\UrlContainerException;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Utils\Registry\BasicRegistry;

class UrlContainer implements UrlContainerInterface
{
    private $entitiesRegistry;

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
        $this->entitiesRegistry = new BasicRegistry;
    }

    /**
     * @return \BetaKiller\IFace\Url\UrlContainerInterface
     */
    public static function create(): UrlContainerInterface
    {
        return new static;
    }

    /**
     * @param \BetaKiller\IFace\Url\UrlParameterInterface $object
     * @param bool|null                                   $ignoreDuplicate
     *
     * @return \BetaKiller\IFace\Url\UrlContainerInterface
     */
    public function setParameter(UrlParameterInterface $object, ?bool $ignoreDuplicate = null): UrlContainerInterface
    {
        $key = $object::getUrlParametersKey();
        $this->entitiesRegistry->set($key, $object, $ignoreDuplicate);

        return $this;
    }

    /**
     * @param string $key
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|null
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
     * @return \BetaKiller\IFace\Url\UrlParameterInterface|null
     */
    public function getParameter(string $key): ?UrlParameterInterface
    {
        return $this->entitiesRegistry->get($key);
    }

    /**
     * @param string|\BetaKiller\Model\DispatchableEntityInterface $className
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|mixed|null
     */
    public function getEntityByClassName($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }

        if (!is_a($className, DispatchableEntityInterface::class, true)) {
            throw new UrlContainerException('Class :name must be instance of :must', [
                ':name' => $className,
                ':must' => DispatchableEntityInterface::class,
            ]);
        }

        /** @var \BetaKiller\Model\DispatchableEntityInterface $className Hack for autocomplete */
        $key = $className::getUrlParametersKey();

        return $this->getEntity($key);
    }

    /**
     * @return \BetaKiller\IFace\Url\UrlContainerInterface
     * @deprecated Url dispatching must be persistent
     */
    public function clear(): UrlContainerInterface
    {
        $this->entitiesRegistry->clear();

        return $this;
    }

    /**
     * @return \BetaKiller\Model\DispatchableEntityInterface[]
     */
    public function getAllParameters(): array
    {
        return $this->entitiesRegistry->getAll();
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasParameter(string $key): bool
    {
        return $this->entitiesRegistry->has($key);
    }

    /**
     * Returns keys of currently added items
     *
     * @return string[]
     */
    public function parametersKeys(): array
    {
        return $this->entitiesRegistry->keys();
    }

    /**
     * Set query parts fetched from current HTTP request
     *
     * @param array $parts
     *
     * @return \BetaKiller\IFace\Url\UrlContainerInterface
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
