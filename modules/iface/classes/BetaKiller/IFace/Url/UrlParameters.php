<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\IFace\Exception\UrlParametersException;
use BetaKiller\Utils\Registry\BasicRegistry;

class UrlParameters implements UrlParametersInterface
{
    private $entitiesRegistry;

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
    public static function create()
    {
        return new static;
    }

    /**
     * @param \BetaKiller\IFace\Url\DispatchableEntityInterface $object
     * @param bool|null                                         $ignoreDuplicate
     *
     * @return $this
     * @throws \Exception
     */
    public function setEntity(DispatchableEntityInterface $object, $ignoreDuplicate = null)
    {
        $key = $object::getUrlParameterKey();
        $this->entitiesRegistry->set($key, $object, (bool)$ignoreDuplicate);

        return $this;
    }

    /**
     * @param string $key
     *
     * @return \BetaKiller\IFace\Url\DispatchableEntityInterface|null
     */
    public function getEntity($key)
    {
        return $this->entitiesRegistry->get($key);
    }

    /**
     * @param string|\BetaKiller\IFace\Url\DispatchableEntityInterface $className
     *
     * @return \BetaKiller\IFace\Url\DispatchableEntityInterface|mixed|null
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

        /** @var \BetaKiller\IFace\Url\DispatchableEntityInterface $className Hack for autocomplete */
        $key = $className::getUrlParameterKey();

        return $this->getEntity($key);
    }

    /**
     * @return $this
     * @deprecated Url dispatching must be persistent
     */
    public function clear()
    {
        $this->entitiesRegistry->clear();

        return $this;
    }

    /**
     * @return \BetaKiller\IFace\Url\DispatchableEntityInterface[]
     */
    public function getAllEntities()
    {
        return $this->entitiesRegistry->getAll();
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasEntity($key)
    {
        return $this->entitiesRegistry->has($key);
    }

    /**
     * Returns keys of currently added items
     *
     * @return string[]
     */
    public function entitiesKeys()
    {
        return $this->entitiesRegistry->keys();
    }
}
