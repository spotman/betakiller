<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\Utils\Registry\BasicRegistry;

class UrlParameters implements UrlParametersInterface
{
    private $registry;

    /**
     * UrlParameters constructor.
     */
    public function __construct()
    {
        $this->registry = new BasicRegistry;
    }

    public static function create()
    {
        return new static;
    }

    /**
     * @param string                 $key
     * @param UrlDataSourceInterface $object
     * @param bool|false             $ignoreDuplicate
     *
     * @return $this
     * @throws \Exception
     */
    public function set($key, UrlDataSourceInterface $object, $ignoreDuplicate = false)
    {
        $key = $object->getCustomUrlParametersKey() ?: $key;

        $this->registry->set($key, $object, $ignoreDuplicate);

        return $this;
    }

    /**
     * @param string $key
     * @return UrlDataSourceInterface|null
     */
    public function get($key)
    {
        return $this->registry->get($key);
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->registry->clear();
        return $this;
    }

    /**
     * @return UrlDataSourceInterface[]
     */
    public function getAll()
    {
        return $this->registry->getAll();
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->registry->has($key);
    }

    /**
     * Returns keys of currently added items
     *
     * @return string[]
     */
    public function keys()
    {
        return $this->registry->keys();
    }

    /**
     * Retrieve an external iterator
     *
     * @return \Traversable|\BetaKiller\IFace\Url\UrlDataSourceInterface[]
     */
    public function getIterator()
    {
        return $this->registry->getIterator();
    }
}
