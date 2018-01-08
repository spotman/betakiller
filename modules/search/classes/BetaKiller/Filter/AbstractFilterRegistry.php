<?php
namespace BetaKiller\Filter;

use BetaKiller\Filter\Model\ApplicableFilterModelInterface;
use BetaKiller\URL\QueryConverter\ConvertibleHelperTrait;
use BetaKiller\URL\QueryConverter\ConvertibleInterface;
use BetaKiller\URL\QueryConverter\ConvertibleItemInterface;
use BetaKiller\Utils\Registry\BasicRegistry;
use Traversable;

abstract class AbstractFilterRegistry implements \IteratorAggregate, ConvertibleInterface
{
    use ConvertibleHelperTrait;

    /**
     * @var \BetaKiller\Utils\Registry\BasicRegistry
     */
    protected $registry;

    /**
     * @var FilterFactory
     */
    protected $filterFactory;

    /**
     * @param FilterFactory $filterFactory
     */
    public function setFilterFactory(FilterFactory $filterFactory)
    {
        $this->filterFactory = $filterFactory;
    }

    /**
     * Returns current filter factory instance
     *
     * @return FilterFactory
     */
    protected function getFilterFactory()
    {
        // Use default filter factory if none provided
        if (!$this->filterFactory) {
            $this->filterFactory = $this->getDefaultFilterFactory();
        }

        return $this->filterFactory;
    }

    /**
     * Returns default filter factory instance
     *
     * @return FilterFactory
     */
    protected function getDefaultFilterFactory()
    {
        return new FilterFactory;
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
     * @param ApplicableFilterModelInterface $model
     *
     * @return $this|static|\BetaKiller\Filter\AbstractFilterRegistry
     */
    public function apply(Model\ApplicableFilterModelInterface $model)
    {
        foreach ( $this->getAll() as $instance ) {
            $instance->apply($model);
        }

        return $this;
    }

    /**
     * @param $key
     *
     * @return \BetaKiller\Filter\FilterInterface
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
     * @return \BetaKiller\URL\QueryConverter\ConvertibleItemInterface
     */
    public function getItemByQueryKey(string $key): ConvertibleItemInterface
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
     * @return \BetaKiller\Utils\Registry\BasicRegistry
     */
    protected function getRegistry()
    {
        if (!$this->registry) {
            $this->registry = new BasicRegistry;
        }

        return $this->registry;
    }

    protected function add(FilterInterface $filter)
    {
        $codename = $filter->getCodename();
        $this->getRegistry()->set($codename, $filter);
    }

    /**
     * @return ConvertibleInterface
     */
    protected function getUrlQueryConverterConvertible()
    {
        return $this;
    }

}
