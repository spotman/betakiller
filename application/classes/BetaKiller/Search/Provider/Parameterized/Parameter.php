<?php
namespace BetaKiller\Search\Provider\Parameterized;

use \BetaKiller\Filter;
use \BetaKiller\Search;
use \BetaKiller\URL\QueryConverter;

interface Parameter extends QueryConverter\ConvertibleItem
{
    /**
     * Set up internal data from array
     *
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data);

    /**
     * Returns internal data as array
     *
     * @return array
     */
    public function asArray();

    /**
     * @return string
     */
    public function getCodename();

    /**
     * Applies current filters to model
     *
     * @param Search\Model\Applicable $model
     */
    public function apply(Search\Model\Applicable $model);

    /**
     * Returns array of values groups (optional filtering by value for autocomplete)
     *
     * @param string|null $filterHaving
     * @return Filter\Model\ValuesGroup[]
     */
    public function getAvailableValues($filterHaving = NULL);

    /**
     * Returns array of selected values groups
     *
     * @return Filter\Model\ValuesGroup[]
     */
    public function getSelectedValues();

    /**
     * Returns true if parameter`s values population is enabled
     *
     * @return bool
     */
    public function isValuesPopulationAllowed();

    /**
     * Returns true if parameter was previously selected (optional filtering via key)
     *
     * @return bool
     */
    public function isSelected();
}
