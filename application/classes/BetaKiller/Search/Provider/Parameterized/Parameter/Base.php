<?php
namespace BetaKiller\Search\Provider\Parameterized\Parameter;

use BetaKiller\Search;
use BetaKiller\Search\Provider\Parameterized\Parameter;
use BetaKiller\Model;
use BetaKiller\Filter;
use BetaKiller\FilterInterface;

abstract class Base implements Parameter
{
    /**
     * @var \BetaKiller\Model\User
     */
    protected $_user;

    /**
     * @var Filter\Factory
     */
    protected $_filterFactory;

    /**
     * @var FilterInterface
     */
    protected $_filterInstance;

    /**
     * Parameter constructor.
     *
     * @param Model\User $_user
     */
    public function __construct(Model\User $_user = NULL)
    {
        $this->_user = $_user;
    }

    /**
     * @return Filter\Factory
     */
    public function getFilterFactory()
    {
        return $this->_filterFactory;
    }

    /**
     * @param Filter\Factory $filterFactory
     */
    public function setFilterFactory(Filter\Factory $filterFactory)
    {
        $this->_filterFactory = $filterFactory;
    }

    /**
     * @return string
     */
    public function getCodename()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * Returns internal data as array
     *
     * @return array
     */
    public function asArray()
    {
        return $this->getFilter()->asArray();
    }

    /**
     * Set up internal data from array
     *
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data)
    {
        $this->getFilter()->fromArray($data);

        return $this;
    }

    /**
     * Applies current filters to model
     *
     * @param Search\Model\Applicable $model
     */
    public function apply(Search\Model\Applicable $model)
    {
        $this->getFilter()->apply($model);
    }

    /**
     * Returns array of values or values groups
     *
     * @param string|null $filterHaving
     * @return \BetaKiller\Filter\Model\ValuesGroup[]
     * @throws \BetaKiller\Search\Provider\Parameterized\Parameter\Exception
     */
    public function getAvailableValues($filterHaving = null)
    {
        if (!$this->isValuesPopulationAllowed()) {
            return [];
        }

        return $this->getFilter()->getAvailableValues($filterHaving);
    }

    /**
     * @param string|null $filterHaving
     * @param bool        $filterSelected
     * @return \BetaKiller\Filter\Model\Value|null
     * @throws \BetaKiller\Search\Provider\Parameterized\Parameter\Exception
     */
    public function getRandomAvailableValue($filterHaving = null, $filterSelected = false)
    {
        if (!$this->isValuesPopulationAllowed()) {
            return null;
        }

        return $this->getFilter()->getRandomAvailableValue($filterHaving);
    }

    /**
     * Returns array of selected values groups
     *
     * @return Filter\Model\ValuesGroup[]
     */
    public function getSelectedValues()
    {
        if (!$this->isValuesPopulationAllowed()) {
            return [];
        }

        return $this->getFilter()->getSelectedValues();
    }

    /**
     * Returns true if parameter`s values population is enabled
     *
     * @return bool
     */
    public function isValuesPopulationAllowed()
    {
        return $this->getFilter()->isValuesPopulationAllowed();
    }

    /**
     * Returns true if parameter was previously selected (optional filtering via key)
     *
     * @param mixed|null $value
     * @return bool
     * @throws \BetaKiller\Search\Provider\Parameterized\Parameter\Exception
     */
    public function isSelected($value = null)
    {
        return $this->getFilter()->isSelected($value);
    }

    public function getUrlQueryKey()
    {
        return $this->getFilter()->getUrlQueryKey();
    }

    public function setUrlQueryKey($value)
    {
        // Empty, codename is given from class name
    }

    public function getUrlQueryValues()
    {
        return $this->getFilter()->getUrlQueryValues();
    }

    public function setUrlQueryValues(array $values)
    {
        $this->getFilter()->setUrlQueryValues($values);
    }

    /**
     * @return \BetaKiller\Filter\Base|\BetaKiller\FilterInterface
     * @throws \BetaKiller\Search\Provider\Parameterized\Parameter\Exception
     */
    protected function getFilter()
    {
        if (!$this->_filterInstance) {
            $factory = $this->getFilterFactory();

            if (!$factory) {
                throw new Parameter\Exception('Set filter factory instance first');
            }

            $codename              = $this->getFilterCodename();
            $this->_filterInstance = $factory->create($codename);
        }

        return $this->_filterInstance;
    }

    protected function getFilterCodename()
    {
        // Use parameter codename
        return $this->getCodename();
    }
}
