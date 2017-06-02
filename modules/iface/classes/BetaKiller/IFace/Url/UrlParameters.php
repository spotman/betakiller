<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\IFace\Exception\UrlParametersException;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Utils\Registry\BasicRegistry;

class UrlParameters implements UrlParametersInterface
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
     * UrlParameters constructor.
     */
    public function __construct()
    {
        $this->entitiesRegistry = new BasicRegistry;
    }

    /**
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public static function create(): UrlParametersInterface
    {
        return new static;
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $object
     * @param bool|null                                     $ignoreDuplicate
     *
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function setEntity(DispatchableEntityInterface $object, ?bool $ignoreDuplicate = null): UrlParametersInterface
    {
        $key = $object::getUrlParametersKey();
        $this->entitiesRegistry->set($key, $object, (bool)$ignoreDuplicate);

        return $this;
    }

    /**
     * @param string $key
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|null
     */
    public function getEntity(string $key): ?DispatchableEntityInterface
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
            throw new UrlParametersException('Class :name must be instance of :must', [
                ':name' => $className,
                ':must' => DispatchableEntityInterface::class,
            ]);
        }

        /** @var \BetaKiller\Model\DispatchableEntityInterface $className Hack for autocomplete */
        $key = $className::getUrlParametersKey();

        return $this->getEntity($key);
    }

    /**
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     * @deprecated Url dispatching must be persistent
     */
    public function clear(): UrlParametersInterface
    {
        $this->entitiesRegistry->clear();

        return $this;
    }

    /**
     * @return \BetaKiller\Model\DispatchableEntityInterface[]
     */
    public function getAllEntities(): array
    {
        return $this->entitiesRegistry->getAll();
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasEntity(string $key): bool
    {
        return $this->entitiesRegistry->has($key);
    }

    /**
     * Returns keys of currently added items
     *
     * @return string[]
     */
    public function entitiesKeys(): array
    {
        return $this->entitiesRegistry->keys();
    }

    /**
     * Set query parts fetched from current HTTP request
     *
     * @param array $parts
     *
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function setQueryParts(array $parts): UrlParametersInterface
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
            throw new UrlParametersException('Missing [:key] query part', [':key' => $key]);
        }

        return null;
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
