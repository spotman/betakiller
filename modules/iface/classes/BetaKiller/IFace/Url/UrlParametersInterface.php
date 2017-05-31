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
    public static function create();

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $object
     * @param bool|false                                    $ignoreDuplicate
     *
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function setEntity(DispatchableEntityInterface $object, $ignoreDuplicate = false);

    /**
     * @param string $key
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|mixed|null
     */
    public function getEntity($key);

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|string $className
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|mixed|null
     */
    public function getEntityByClassName($className);

    /**
     * @return \BetaKiller\Model\DispatchableEntityInterface[]
     */
    public function getAllEntities();

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasEntity($key);

    /**
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     * @deprecated Url parameters must be persistent
     */
    public function clear();

    /**
     * Returns keys of currently added Entity items
     *
     * @return string[]
     */
    public function entitiesKeys();
}
