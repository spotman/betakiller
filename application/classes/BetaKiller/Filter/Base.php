<?php
namespace BetaKiller\Filter;

use \BetaKiller\Filter;
use BetaKiller\Filter\Model\Value;
use BetaKiller\Model\User;

abstract class Base implements Filter
{
    /**
     * @var User
     */
    protected $_user;

    /**
     * Base constructor.
     *
     * @param \BetaKiller\Model\User $_user
     */
    public function __construct(User $_user = null)
    {
        $this->_user = $_user;
    }

    /**
     * @return string
     */
    public function getCodename()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    protected function availableValueFactory($key, $label)
    {
        return Filter\Model\Value::factory($key, $label, $this->isSelected($key));
    }

    /**
     * @param string    $label
     * @param Value[]   $values
     * @return static
     */
    protected function availableValuesGroupFactory($label, array $values)
    {
        return Filter\Model\ValuesGroup::factory($label, $values);
    }

    /**
     * @return \Model_User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Returns array of values or grouped values
     *
     * @param string|null $filterHaving
     * @return \BetaKiller\Filter\Model\ValuesGroup[]
     */
    public function getAvailableValues($filterHaving = null)
    {
        if (!$this->isValuesPopulationAllowed())
            return [];

        $pairs = $this->getAvailableValuesPairs($filterHaving);

        return $this->processValuesPairs($pairs);
    }

    /**
     * @param null $filterHaving
     * @param bool $filterSelected
     * @return \BetaKiller\Filter\Model\Value[]|null
     */
    public function getRandomAvailableValue($filterHaving = null, $filterSelected = false)
    {
        $valuesGroups = $this->getAvailableValues($filterHaving);

        if (!$valuesGroups)
            return null;

        $groupIndex = array_rand($valuesGroups);

        /** @var Filter\Model\ValuesGroup $group */
        $group = $valuesGroups[$groupIndex];

        $values = $group->getValues();

        if (!$values)
            return null;

        $valueIndex = array_rand($values);

        return $values[$valueIndex];
    }

    private function processValuesPairs(array $pairs)
    {
        if (!$pairs)
            return [];

        if ($this->hasGroupedValues()) {
            return $this->wrapGroupedValues($pairs);
        } else {
            // Force wrapping into ValuesGroup with null label
            $values = $this->wrapValuesPairs($pairs);
            return [
                $this->availableValuesGroupFactory(null, $values)
            ];
        }
    }

    private function wrapValuesPairs(array $pairs)
    {
        $values = [];

        foreach ($pairs as $key => $label) {
            $values[] = $this->availableValueFactory($key, $label);
        }

        return $values;
    }

    private function wrapGroupedValues(array $pairs)
    {
        $values = [];

        foreach ($pairs as $groupLabel => $groupPairs) {
            $values[] = $this->availableValuesGroupFactory(
                $groupLabel,
                $this->wrapValuesPairs($groupPairs)
            );
        }

        return $values;
    }

    /**
     * Returns array of values with structure like <key> => <label>
     *
     * @param string|null $filterHaving
     * @return array
     */
    abstract protected function getAvailableValuesPairs($filterHaving = null);

    /**
     * Returns array of selected values groups
     *
     * @return Filter\Model\ValuesGroup[]
     */
    public function getSelectedValues()
    {
        // Skip empty filters
        if (!$this->isSelected())
            return [];

        if (!$this->isValuesPopulationAllowed())
            return [];

        $pairs = $this->getSelectedValuesPairs();

        return $this->processValuesPairs($pairs);
    }

    /**
     * Returns array of values with structure like <key> => <label>
     *
     * @return array
     */
    abstract protected function getSelectedValuesPairs();

    /**
     * Returns true if filter`s values population is enabled
     *
     * @return bool
     */
    public function isValuesPopulationAllowed()
    {
        // Enabled by default
        return true;
    }

    /**
     * Returns true if getAvailableValues returns grouped values (nested arrays)
     *
     * @return bool
     */
    public function hasGroupedValues()
    {
        // No grouped values by default
        return false;
    }

    public function getUrlQueryKey()
    {
        return lcfirst($this->getCodename());
    }

    public function setUrlQueryKey($value)
    {
        // Empty, codename is given from class name automatically
    }

    /**
     * Returns true if current item is usable for url converting
     *
     * @return bool
     */
    public function isUrlConversionAllowed()
    {
        // Is convertible by default
        return true;
    }

}
