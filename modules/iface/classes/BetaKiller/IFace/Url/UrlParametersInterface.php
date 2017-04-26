<?php
namespace BetaKiller\IFace\Url;

interface UrlParametersInterface
{
    /**
     * Retrieve an external iterator
     *
     * @return \Traversable|\BetaKiller\IFace\Url\UrlDataSourceInterface[]
     */
    public function getIterator();

    /**
     * @param string                 $key
     * @param UrlDataSourceInterface $object
     * @param bool|false             $ignoreDuplicate
     *
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function set($key, UrlDataSourceInterface $object, $ignoreDuplicate = false);

    /**
     * @param string $key
     * @return UrlDataSourceInterface|null
     */
    public function get($key);

    /**
     * @return \BetaKiller\IFace\Url\UrlParametersInterface
     */
    public function clear();

    /**
     * @return UrlDataSourceInterface[]
     */
    public function getAll();

    /**
     * @param string $key
     * @return bool
     */
    public function has($key);

    /**
     * Returns keys of currently added items
     *
     * @return string[]
     */
    public function keys();
}
