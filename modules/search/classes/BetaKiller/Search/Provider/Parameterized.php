<?php
namespace BetaKiller\Search\Provider;

use BetaKiller\Filter\Model\ValuesGroup;
use BetaKiller\Search\ApplicableSearchModelInterface;
use BetaKiller\Search\Provider\Parameterized\Parameter\Registry;
use BetaKiller\Search\SearchResultsInterface;

abstract class Parameterized extends AbstractProvider
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
     * @param int      $page
     * @param int|null $itemsPerPage
     *
     * @return \BetaKiller\Search\SearchResultsInterface
     * @throws \BetaKiller\Search\Provider\SearchProviderException
     */
    public function getResults(int $page, int $itemsPerPage): SearchResultsInterface
    {
        if (!$this->getCurrentParametersRegistry()->hasSelectedParameters()) {
            return $this->getEmptyResults();
        }

        $model = $this->searchModelFactory();

        $this->apply($model);

        $result = $model->getSearchResults($page, $itemsPerPage);

        $url = $this->makeResultsUrl();
        $result->setUrl($url);

        return $result;
    }

    /**
     * @return string
     * @throws \BetaKiller\Search\Provider\SearchProviderException
     */
    protected function makeResultsUrl(): string
    {
        $url         = $this->getBaseResultsUrl();
        $queryString = $this->getCurrentParametersRegistry()->toUrlQueryString();

        $result = $queryString
            ? http_build_url($url, '?'.$queryString, HTTP_URL_JOIN_QUERY)
            : $url;

        if (!$result) {
            throw new SearchProviderException('Url creating failed with [:url] and [:query]', [
                ':url'   => $url,
                ':query' => $queryString,
            ]);
        }

        return $result;
    }

    /**
     * @return string
     */
    abstract protected function getBaseResultsUrl(): string;

    /**
     * @return \BetaKiller\Search\Provider\Parameterized\Parameter\Registry
     * @throws \BetaKiller\Search\Provider\SearchProviderException
     */
    protected function getCurrentParametersRegistry(): Registry
    {
        if (!$this->currentParametersRegistry) {
            throw new SearchProviderException('Select parameters registry before applying parameters');
        }

        return $this->currentParametersRegistry;
    }

    /**
     * @param string $codename
     *
     * @throws \BetaKiller\Search\Provider\SearchProviderException
     */
    public function setCurrentParametersRegistry(string $codename)
    {
        if (!isset($this->parametersRegistries[$codename])) {
            throw new SearchProviderException('No registry found by codename :codename', [':codename' => $codename]);
        }

        $this->currentParametersRegistry = $this->parametersRegistries[$codename];
    }

    /**
     * @param string                                                       $codename
     * @param \BetaKiller\Search\Provider\Parameterized\Parameter\Registry $parametersRegistry
     */
    public function addParametersRegistry(string $codename, Registry $parametersRegistry)
    {
        $this->parametersRegistries[$codename] = $parametersRegistry;
    }

    /**
     * @param array $query
     */
    public function fromUrlQueryArray(array $query)
    {
        foreach ($this->parametersRegistries as $registry) {
            $registry->fromUrlQueryArray($query);
        }
    }

    /**
     * @return array
     * @throws \BetaKiller\Search\Provider\SearchProviderException
     */
    public function toUrlQueryArray(): array
    {
        return $this->getCurrentParametersRegistry()->toUrlQueryArray();
    }

    /**
     * @return string
     * @throws \BetaKiller\Search\Provider\SearchProviderException
     */
    public function toUrlQueryString(): string
    {
        return $this->getCurrentParametersRegistry()->toUrlQueryString();
    }

    /**
     * @param string|null $filterHaving
     *
     * @return ValuesGroup[]
     * @throws \BetaKiller\Search\Provider\SearchProviderException
     */
    public function getAvailableValues($filterHaving = null): array
    {
        $values = [];

        foreach ($this->parametersRegistries as $registry) {
            $values[] = $this->makeRegistryValues($registry->getAvailableValues($filterHaving));
        }

        return array_merge(...$values);
    }

    /**
     * @return ValuesGroup[]
     * @throws \BetaKiller\Search\Provider\SearchProviderException
     */
    public function getSelectedValues(): array
    {
        $values = [];

        foreach ($this->parametersRegistries as $registry) {
            $values[] = $this->makeRegistryValues($registry->getSelectedValues());
        }

        return array_merge(...$values);
    }

    /**
     * @param ValuesGroup[] $valuesGroups
     *
     * @return ValuesGroup[]
     * @throws \BetaKiller\Search\Provider\SearchProviderException
     */
    protected function makeRegistryValues(array $valuesGroups): array
    {
        $values = [];

        foreach ($valuesGroups as $group) {
            $codename = $group->getCodename();
            $label    = $group->getLabel();

            if (!$codename) {
                throw new SearchProviderException('ValuesGroup [:label] codename is empty', [':label' => $label]);
            }

            // Make compound key for preventing duplicates
            $key = $codename.'-'.$label;

            $values[$key] = $group;
        }

        return $values;
    }

    /**
     * @param \BetaKiller\Search\ApplicableSearchModelInterface $model
     *
     * @throws \BetaKiller\Search\Provider\SearchProviderException
     */
    protected function apply(ApplicableSearchModelInterface $model)
    {
        $this->getCurrentParametersRegistry()->apply($model);
    }

    /**
     * @return \BetaKiller\Search\ApplicableSearchModelInterface|mixed
     */
    abstract protected function searchModelFactory();
}
