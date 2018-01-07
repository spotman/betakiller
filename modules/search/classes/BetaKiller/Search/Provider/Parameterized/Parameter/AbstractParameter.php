<?php
namespace BetaKiller\Search\Provider\Parameterized\Parameter;

use BetaKiller\Filter\FilterFactory;
use BetaKiller\Filter\FilterInterface;
use BetaKiller\Filter\Model\Value;
use BetaKiller\Model\UserInterface;
use BetaKiller\Search\ApplicableSearchModelInterface;
use BetaKiller\Search\Provider\Parameterized\ParameterInterface;

abstract class AbstractParameter implements ParameterInterface
{
    /**
     * @var \BetaKiller\Model\User
     */
    protected $_user;

    /**
     * @var \BetaKiller\Filter\FilterFactory
     */
    protected $_filterFactory;

    /**
     * @var FilterInterface
     */
    protected $_filterInstance;

    /**
     * ParameterInterface constructor.
     *
     * @param \BetaKiller\Model\UserInterface $_user
     */
    public function __construct(UserInterface $_user = null)
    {
        $this->_user = $_user;
    }

    /**
     * @return \BetaKiller\Filter\FilterFactory
     */
    public function getFilterFactory(): FilterFactory
    {
        return $this->_filterFactory;
    }

    /**
     * @param \BetaKiller\Filter\FilterFactory $filterFactory
     */
    public function setFilterFactory(FilterFactory $filterFactory)
    {
        $this->_filterFactory = $filterFactory;
    }

    /**
     * @return string
     */
    public function getCodename(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * Returns internal data as array
     *
     * @return array
     */
    public function asArray(): array
    {
        return $this->getFilter()->asArray();
    }

    /**
     * Set up internal data from array
     *
     * @param array $data
     *
     * @return ParameterInterface
     */
    public function fromArray(array $data): ParameterInterface
    {
        $this->getFilter()->fromArray($data);

        return $this;
    }

    /**
     * Applies current filters to model
     *
     * @param \BetaKiller\Search\ApplicableSearchModelInterface $model
     */
    public function apply(ApplicableSearchModelInterface $model): void
    {
        $this->getFilter()->apply($model);
    }

    /**
     * Returns array of values or values groups
     *
     * @param string|null $filterHaving
     *
     * @return \BetaKiller\Filter\Model\ValuesGroup[]
     * @throws \BetaKiller\Search\Provider\Parameterized\Parameter\Exception
     */
    public function getAvailableValues($filterHaving = null): array
    {
        if (!$this->isValuesPopulationAllowed()) {
            return [];
        }

        return $this->getFilter()->getAvailableValues($filterHaving);
    }

    /**
     * @param string|null $filterHaving
     * @param bool|null   $filterSelected
     *
     * @return \BetaKiller\Filter\Model\Value|null
     * @throws \BetaKiller\Search\Provider\Parameterized\Parameter\Exception
     */
    public function getRandomAvailableValue($filterHaving = null, ?bool $filterSelected = null): ?Value
    {
        if (!$this->isValuesPopulationAllowed()) {
            return null;
        }

        return $this->getFilter()->getRandomAvailableValue($filterHaving);
    }

    /**
     * Returns array of selected values groups
     *
     * @return \BetaKiller\Filter\Model\ValuesGroup[]
     */
    public function getSelectedValues(): array
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
    public function isValuesPopulationAllowed(): bool
    {
        return $this->getFilter()->isValuesPopulationAllowed();
    }

    /**
     * Returns true if parameter was previously selected (optional filtering via key)
     *
     * @param mixed|null $value
     *
     * @return bool
     * @throws \BetaKiller\Search\Provider\Parameterized\Parameter\Exception
     */
    public function isSelected($value = null): bool
    {
        return $this->getFilter()->isSelected($value);
    }

    public function getUrlQueryKey(): string
    {
        return $this->getFilter()->getUrlQueryKey();
    }

    public function setUrlQueryKey($value): void
    {
        // Empty, codename is given from class name
    }

    public function getUrlQueryValues()
    {
        return $this->getFilter()->getUrlQueryValues();
    }

    public function setUrlQueryValues(array $values): void
    {
        $this->getFilter()->setUrlQueryValues($values);
    }

    /**
     * @return \BetaKiller\Filter\AbstractFilter|\BetaKiller\Filter\FilterInterface
     * @throws \BetaKiller\Search\Provider\Parameterized\Parameter\Exception
     */
    protected function getFilter(): FilterInterface
    {
        if (!$this->_filterInstance) {
            $factory = $this->getFilterFactory();

            if (!$factory) {
                throw new Exception('Set filter factory instance first');
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
