<?php
namespace BetaKiller\Filter;

use BetaKiller\Filter\Model\ApplicableFilterModelInterface;
use BetaKiller\Filter\Model\Value;
use BetaKiller\URL\QueryConverter\ConvertibleItemInterface;

interface FilterInterface extends ConvertibleItemInterface
{
    /**
     * Set up internal data from array
     *
     * @param array $config
     *
     * @return $this
     */
    public function fromArray(array $config);

    /**
     * Returns internal data as array
     *
     * @return array
     */
    public function asArray(): array;

    public function apply(ApplicableFilterModelInterface $model): void;

    /**
     * @return string
     */
    public function getCodename(): string;

    /**
     * Returns array of values (<value> => <label>)
     *
     * @param string|null $filterHaving
     *
     * @return \BetaKiller\Filter\Model\ValuesGroup[]
     */
    public function getAvailableValues($filterHaving = null): array;

    /**
     * @param null      $filterHaving
     * @param bool|null $filterSelected
     *
     * @return \BetaKiller\Filter\Model\Value
     */
    public function getRandomAvailableValue($filterHaving = null, ?bool $filterSelected = null): Value;

    /**
     * Returns array of selected values groups
     *
     * @return \BetaKiller\Filter\Model\ValuesGroup[]
     */
    public function getSelectedValues(): array;

    /**
     * Returns true if filter`s values population is enabled
     *
     * @return bool
     */
    public function isValuesPopulationAllowed(): bool;

    /**
     * Returns true if filter was previously selected (optional filtering via key)
     *
     * @param string|int|null $value
     *
     * @return bool
     */
    public function isSelected($value = null): bool;

    /**
     * Returns true if getAvailableValues returns grouped values (nested arrays)
     *
     * @return bool
     */
    public function hasGroupedValues(): bool;
}
