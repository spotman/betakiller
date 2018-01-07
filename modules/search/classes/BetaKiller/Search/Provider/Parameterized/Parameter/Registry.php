<?php
namespace BetaKiller\Search\Provider\Parameterized\Parameter;

use BetaKiller\Filter\Model\ValuesGroup;
use BetaKiller\Model\User;
use BetaKiller\Search;
use BetaKiller\Search\Provider\Parameterized\ParameterInterface;
use BetaKiller\URL\QueryConverter;
use BetaKiller\URL\QueryConverter\ConvertibleInterface;
use BetaKiller\URL\QueryConverter\ConvertibleItemInterface;
use BetaKiller\Utils;
use Traversable;

abstract class Registry implements \IteratorAggregate, QueryConverter\ConvertibleInterface
{
    use Utils\Instance\Simple,
        QueryConverter\ConvertibleHelper;

    /**
     * @var \BetaKiller\Model\User
     * @deprecated
     * @todo DI
     */
    protected $_user;

    /**
     * @var Provider\Parameterized\Parameter\Factory
     */
    protected $_parameterFactory;

    /**
     * @var Utils\Registry\BasicRegistry
     */
    protected $_registry;

    public function __construct()
    {
        $this->_registry = new Utils\Registry\BasicRegistry();
    }

    /**
     * Custom initialization (add parameters, configure registry, etc)
     */
    abstract public function init();

    /**
     * @param \BetaKiller\Model\User|NULL $user
     *
     * @return $this
     * @deprecated
     * @todo DI
     */
    public function setUser(User $user = NULL)
    {
        $this->_user = $user;
        return $this;
    }

    /**
     * @param Provider\Parameterized\Parameter\Factory $parameterFactory
     * @return $this
     */
    public function setParameterFactory(Provider\Parameterized\Parameter\Factory $parameterFactory)
    {
        $this->_parameterFactory = $parameterFactory;
        return $this;
    }

    public function addParameter($codename)
    {
        return $this->getParameter($codename);
    }

    /**
     * @param $codename
     *
     * @return Provider\Parameterized\ParameterInterface
     * @throws Utils\Registry\Exception
     */
    public function getParameter($codename)
    {
        $instance = $this->_registry->get($codename);

        if ( !$instance ) {
            $instance = $this->parameterFactory($codename);
            $this->_registry->set($codename, $instance);
        }

        return $instance;
    }

    /**
     * @return Provider\Parameterized\ParameterInterface[]
     */
    protected function getParameters()
    {
        return $this->_registry->getAll();
    }

    public function fromArray(array $config)
    {
        foreach ($config as $codename => $data) {
            $this->getParameter(ucfirst($codename))->fromArray($data);
        }
    }

    public function asArray()
    {
        $output = [];

        foreach ($this->getParameters() as $param) {
            $codename = mb_strtolower($param->getCodename());
            $output[ $codename ] = $param->asArray();
        }

        return $output;
    }

    public function apply(Search\ApplicableSearchModelInterface $model)
    {
        foreach ($this->getParameters() as $param) {
            // Skip empty parameters
            if ($param->isSelected()) {
                $param->apply($model);
            }
        }
    }

    /**
     * @return bool
     */
    public function hasSelectedParameters()
    {
        foreach ($this->getParameters() as $param) {
            if ($param->isSelected()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|null   $filterHaving
     * @param string        $nsSeparator
     * @return \BetaKiller\Filter\Model\ValuesGroup[]
     */
    public function getAvailableValues($filterHaving = null, $nsSeparator = '-')
    {
        $data = [];

        foreach ($this->getParameters() as $param) {
            $valuesGroups = $param->getAvailableValues($filterHaving);

            if (!$valuesGroups)
                continue;

            $this->presetParametersValuesGroupsCodename($param, $valuesGroups, $nsSeparator);

            $data = array_merge($data, $valuesGroups);
        }

        return $data;
    }

    /**
     * @param string $nsSeparator
     * @return ValuesGroup[]
     */
    public function getSelectedValues($nsSeparator = '-')
    {
        $data = [];

        foreach ($this->getParameters() as $param) {
            $valuesGroups = $param->getSelectedValues();

            // Skip empty parameters
            if (!$valuesGroups)
                continue;

            $this->presetParametersValuesGroupsCodename($param, $valuesGroups, $nsSeparator);

            $data = array_merge($data, $valuesGroups);
        }

        return $data;
    }

    /**
     * @param \BetaKiller\Search\Provider\Parameterized\ParameterInterface $param
     * @param ValuesGroup[]                                                $groups
     * @param string                                                       $nsSeparator
     */
    protected function presetParametersValuesGroupsCodename(ParameterInterface $param, array $groups, $nsSeparator = '-')
    {
        $ns = $this->getUrlQueryKeysNamespace();
        $codename = $param->getUrlQueryKey();

        if ($ns) {
            $codename = $ns.$nsSeparator.$codename;
        }

        foreach ($groups as $group) {
            if (!$group->getCodename())
                $group->setCodename($codename);

            foreach ($group->getValues() as $value) {
                $value->setKeyNamespace($codename);
            }
        }
    }

    /**
     * @param string $key
     *
     * @return ConvertibleItemInterface
     */
    public function getItemByQueryKey(string $key)
    {
        $codename = ucfirst($key);
        return $this->getParameter($codename);
    }

    /**
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable|ParameterInterface[] An instance of an object implementing <b>Iterator</b> or
     *        <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return $this->_registry->getIterator();
    }

    /**
     * @return string
     */
    protected function getParameterNamespace()
    {
        // Empty by default
        return '';
    }

    protected function parameterFactory($codename)
    {
        $ns = $this->getParameterNamespace();

        if ($ns) {
            $codename = $ns.'\\'.$codename;
        }

        $instance = $this->_parameterFactory->create($codename, $this->_user);
        return $instance;
    }

    /**
     * @return ConvertibleInterface
     */
    protected function getUrlQueryConverterConvertible()
    {
        return $this;
    }

}
