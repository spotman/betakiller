<?php
namespace BetaKiller\Search\Provider;

use BetaKiller\Filter\Model\ValuesGroup;
use BetaKiller\Search;
use BetaKiller\Search\Provider;
use BetaKiller\Search\Provider\Parameterized\Parameter\Registry;

//use \AFS\Filter\Registry;

abstract class Parameterized extends Search\Provider
{
    /**
     * @var Registry[]
     */
    protected $parametersRegistries;

    /**
     * @var Registry
     */
    protected $currentParametersRegistry;

    /**
     * @param $page
     * @param int|null $itemsPerPage
     *
     * @return \BetaKiller\Search\SearchResultsInterface|\BetaKiller\Search\SearchResultsItemInterface[]
     * @throws \BetaKiller\Search\Provider\Exception
     */
    public function getResults($page, $itemsPerPage = null)
    {
        if (!$this->getCurrentParametersRegistry()->hasSelectedParameters())
            return $this->getEmptyResults();

        /** @var \ORM $model */
        $model = $this->searchModelFactory();

        $this->apply($model);

        $result = $model->getSearchResults($page, $itemsPerPage);

        $url = $this->makeResultsUrl();
        $result->setURL($url);

//        die($model->last_query());

        return $result;
    }

    /**
     * @return string
     * @throws \BetaKiller\Search\Provider\Exception
     */
    protected function makeResultsUrl()
    {
        $url = $this->getBaseResultsUrl();
        $queryString = $this->getCurrentParametersRegistry()->toUrlQueryString();

        $result = $queryString
            ? http_build_url($url, '?'.$queryString, HTTP_URL_JOIN_QUERY)
            : $url;

        if (!$result)
            throw new Exception('Url creating failed with [:url] and [:query]', [
                ':url'      =>  $url,
                ':query'    =>  $queryString,
            ]);

        return $result;
    }

    /**
     * @return string
     */
    abstract protected function getBaseResultsUrl();

    /**
     * @return \BetaKiller\Search\Provider\Parameterized\Parameter\Registry
     * @throws \BetaKiller\Search\Provider\Exception
     */
    protected function getCurrentParametersRegistry()
    {
        if (!$this->currentParametersRegistry)
            throw new Search\Provider\Exception('Select parameters registry before applying parameters');

        return $this->currentParametersRegistry;
    }

    public function setCurrentParametersRegistry($codename)
    {
        if (!isset($this->parametersRegistries[$codename]))
            throw new Exception('No registry found by codename :codename', [':codename' => $codename]);

        $this->currentParametersRegistry = $this->parametersRegistries[$codename];
    }

    /**
     * @param string                                                       $codename
     * @param \BetaKiller\Search\Provider\Parameterized\Parameter\Registry $parametersRegistry
     */
    public function addParametersRegistry($codename, Provider\Parameterized\Parameter\Registry $parametersRegistry)
    {
        $this->parametersRegistries[$codename] = $parametersRegistry;
    }

    public function fromUrlQueryArray(array $query)
    {
        foreach ($this->parametersRegistries as $registry) {
            $registry->fromUrlQueryArray($query);
        }
    }

    public function toUrlQueryArray()
    {
        return $this->getCurrentParametersRegistry()->toUrlQueryArray();
    }

    public function toUrlQueryString()
    {
        return $this->getCurrentParametersRegistry()->toUrlQueryString();
    }

    /**
     * @param string|null $filterHaving
     * @return ValuesGroup[]
     * @throws \BetaKiller\Search\Provider\Exception
     */
    public function getAvailableValues($filterHaving = null)
    {
        $values = [];

        foreach ($this->parametersRegistries as $registry) {
            $values = array_merge(
                $values,
                $this->makeRegistryValues($registry->getAvailableValues($filterHaving))
            );
        }

        return $values;
    }

    /**
     * @return ValuesGroup[]
     * @throws \BetaKiller\Search\Provider\Exception
     */
    public function getSelectedValues()
    {
        $values = [];

        foreach ($this->parametersRegistries as $registry) {
            $values = array_merge(
                $values,
                $this->makeRegistryValues($registry->getSelectedValues())
            );
        }

        return $values;
    }

    /**
     * @param ValuesGroup[] $valuesGroups
     * @return ValuesGroup[]
     * @throws \BetaKiller\Search\Provider\Exception
     */
    protected function makeRegistryValues(array $valuesGroups)
    {
        $values = [];

        foreach ($valuesGroups as $group) {
            $codename = $group->getCodename();
            $label = $group->getLabel();

            if (!$codename)
                throw new Exception('ValuesGroup [:label] codename is empty', [':label' => $label]);

            // Make compound key for preventing duplicates
            $key = $codename.'-'.$label;

            $values[$key] = $group;
        }

        return $values;
    }

    protected function apply(Search\ApplicableModelInterface $model)
    {
        $this->getCurrentParametersRegistry()->apply($model);
    }

    /**
     * @return \BetaKiller\Search\ApplicableModelInterface
     */
    abstract protected function searchModelFactory();

};
