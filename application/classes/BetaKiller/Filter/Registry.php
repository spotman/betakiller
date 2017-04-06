<?php
namespace BetaKiller\Filter;

use BetaKiller\FilterInterface;
use BetaKiller\Filter\Model\Applicable;
use BetaKiller\URL\QueryConverter\Convertible;
use BetaKiller\Utils;
use BetaKiller\URL\QueryConverter;
use Traversable;

abstract class Registry implements \IteratorAggregate, QueryConverter\Convertible
{
    use Utils\Instance\Simple,
        QueryConverter\ConvertibleHelper;

    /**
     * @var Utils\Registry\Base
     */
    protected $_registry;

    /**
     * @var Filter\Factory
     */
    protected $_filterFactory;

    /**
     * @param Factory $filterFactory
     */
    public function setFilterFactory(Factory $filterFactory)
    {
        $this->_filterFactory = $filterFactory;
    }

    /**
     * Returns current filter factory instance
     *
     * @return Factory
     */
    protected function getFilterFactory()
    {
        // Use default filter factory if none provided
        if (!$this->_filterFactory) {
            $this->_filterFactory = $this->getDefaultFilterFactory();
        }

        return $this->_filterFactory;
    }

    /**
     * Returns default filter factory instance
     *
     * @return Factory
     */
    protected function getDefaultFilterFactory()
    {
        return Factory::instance();
    }

    /**
     * Set up internal data from array
     *
     * @param array $config
     * @return $this
     */
    public function fromArray(array $config)
    {
        // Instantiate all filters
        foreach ( $config as $codename => $data ) {
            $this->add(
                $this->filterFactory($codename)->fromArray((array) $data)
            );
        }

        return $this;
    }

    /**
     * Returns internal data as array
     *
     * @return array
     */
    public function asArray()
    {
        $output = array();

        foreach ( $this->getAll() as $codename => $instance ) {
            $output[$codename] = $instance->asArray();
        }

        return $output;
    }

    /**
     * @param Applicable $model
     * @return $this|static|\BetaKiller\Filter\Registry
     */
    public function apply(Model\Applicable $model)
    {
        foreach ( $this->getAll() as $instance ) {
            $instance->apply($model);
        }

        return $this;
    }

    /**
     * @param $key
     *
     * @return \BetaKiller\FilterInterface
     */
    public function get($key)
    {
        $instance = $this->getRegistry()->get($key);

        if (!$instance) {
            $instance = $this->filterFactory($key);
            $this->add($instance);
        }

        return $instance;
    }

    /**
     * @return FilterInterface[]
     */
    public function getAll()
    {
        return $this->getRegistry()->getAll();
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->getRegistry()->clear();
        return $this;
    }

    /**
     * @param string $key
     * @return QueryConverter\ConvertibleItem
     */
    public function getItemByQueryKey($key)
    {
        $codename = ucfirst($key);
        return $this->get($codename);
    }

    /**
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable|FilterInterface[] An instance of an object implementing <b>Iterator</b> or
     *        <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return $this->getRegistry()->getIterator();
    }

    /**
     * @param $codename
     *
     * @return FilterInterface
     */
    protected function filterFactory($codename)
    {
//        $codename = $this->getFilterNamespace().$codename;
        return $this->getFilterFactory()->create($codename);
    }

    /**
     * @return string
     */
    public function getFilterNamespace()
    {
        // Empty by default
        return '';
    }

    /**
     * @return Utils\Registry\Base
     */
    protected function getRegistry()
    {
        if (!$this->_registry) {
            $this->_registry = Utils\Registry\Base::instance();
        }

        return $this->_registry;
    }

    protected function add(FilterInterface $filter)
    {
        $codename = $filter->getCodename();
        $this->getRegistry()->set($codename, $filter);
    }

    /**
     * @return Convertible
     */
    protected function getUrlQueryConverterConvertible()
    {
        return $this;
    }

}
