<?php
namespace BetaKiller\Search\Provider\Parameterized;

use BetaKiller\Filter\Model\Value;
use BetaKiller\Search\ApplicableModelInterface;
use BetaKiller\URL\QueryConverter\ConvertibleItemInterface;

interface ParameterInterface extends ConvertibleItemInterface
{
    /**
     * Set up internal data from array
     *
     * @param array $data
     *
     * @return $this
     */
    public function fromArray(array $data);

    /**
     * Returns internal data as array
     *
     * @return array
     */
    public function asArray(): array;

    /**
     * @return string
     */
    public function getCodename(): string;

    /**
     * Applies current filters to model
     *
     * @param \BetaKiller\Search\ApplicableModelInterface $model
     */
    public function apply(ApplicableModelInterface $model): void;

    /**
     * Returns array of values groups (optional filtering by value for autocomplete)
     *
     * @param string|null $filterHaving
     *
     * @return \BetaKiller\Filter\Model\ValuesGroup[]
     */
    public function getAvailableValues($filterHaving = null): array;

    /**
     * @param string|null $filterHaving
     * @param bool|null   $filterSelected
     *
     * @return \BetaKiller\Filter\Model\Value
     */
    public function getRandomAvailableValue($filterHaving = null, ?bool $filterSelected = null): ?Value;

    /**
     * Returns array of selected values groups
     *
     * @return \BetaKiller\Filter\Model\ValuesGroup[]
     */
    public function getSelectedValues(): array;

    /**
     * Returns true if parameter`s values population is enabled
     *
     * @return bool
     */
    public function isValuesPopulationAllowed(): bool;

    /**
     * Returns true if parameter was previously selected (optional filtering via key)
     *
     * @return bool
     */
    public function isSelected(): bool;
}
