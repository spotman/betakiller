<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\DI\Container;
use BetaKiller\Factory\OrmFactory;
use BetaKiller\IFace\Url\UrlDataSourceInterface;
use BetaKiller\IFace\Url\UrlParameters;
use BetaKiller\Search\Model\Applicable;
use BetaKiller\Search\Model\ResultsItem;
use BetaKiller\Utils;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use Spotman\Api\ApiResponseItemInterface;

class ORM extends Utils\Kohana\ORM implements ApiResponseItemInterface, UrlDataSourceInterface, Applicable, ResultsItem
{
    /**
     * @var OrmFactory
     */
    protected static $_factory_instance;

    /**
     * @param string $model
     * @param int|array|null   $id
     *
     * @return OrmInterface
     */
    public static function factory($model, $id = NULL)
    {
        // Coz ORM do not cares about letter cases
        $model = str_replace(' ', '_', ucwords(str_replace('_', ' ', $model)));

        // No direct search by ID coz ORM crashes with circular dependencies when extended from TreeModel and initialized with id

        /** @var OrmInterface $object */
        $object = self::getFactory()->create($model);

        // Old Kohana sugar for searching in model
        if (is_array($id)) {
            foreach ($id as $column => $value) {
                // Passing an array of column => values
                $object->where($column, '=', $value);
            }

            return $object->find();
        }

        // Search by ID
        if ($id) {
            return $object->get_by_id($id, true); // Allow missing elements for BC
        }

        // Plain model factory
        return $object;
    }

    protected static function getFactory()
    {
        if (!self::$_factory_instance) {
            /** @var OrmFactory $factory */
            $factory = Container::instance()->get(OrmFactory::class);

            $factory
                ->setExpectedInterface(\Kohana_ORM::class)
                ->setClassPrefixes('Model');

            self::$_factory_instance = $factory;
        }

        return self::$_factory_instance;
    }

    /**
     * Default implementation for ORM objects
     * Override this method in child classes
     *
     * @return array
     */
    public function getApiResponseData()
    {
        return $this->as_array();
    }

    /**
     * Default implementation for ORM objects
     * Override this method in child classes
     *
     * @return DateTime|NULL
     */
    public function getApiLastModified()
    {
        // Empty by default
        return NULL;
    }

    /**
     * Performs search for model item where the $key property value is equal to $value
     *
     * @param string        $key
     * @param string        $value
     * @param UrlParameters $parameters
     *
     * @return UrlDataSourceInterface|NULL
     */
    public function find_by_url_key($key, $value, UrlParameters $parameters)
    {
        // Additional filtering for non-pk keys
        if ( $key != $this->primary_key() )
        {
            $this->custom_find_by_url_filter($parameters);
        }

        $model = $this->where($this->object_column($key), '=', $value)->find();

        return $model->loaded() ? $model : NULL;
    }

    /**
     * @param UrlParameters $parameters
     */
    protected function custom_find_by_url_filter(UrlParameters $parameters)
    {
        // Empty by default
    }

    public function get_default_url_value()
    {
        return 'index';
    }

    /**
     * Returns value of the $key property
     * @param string $key
     * @return string
     */
    public function get_url_key_value($key)
    {
        return (string) $this->get($key);
    }

    /**
     * Returns list of available items (model records) by $key property
     *
     * @param string        $key
     * @param UrlParameters $parameters
     * @param int|null      $limit
     *
     * @return \BetaKiller\IFace\Url\UrlDataSourceInterface[]
     */
    public function get_available_items_by_url_key($key, UrlParameters $parameters, $limit = NULL)
    {
        // Additional filtering for non-pk keys
        if ( $key != $this->primary_key() )
        {
            $this->custom_find_by_url_filter($parameters);
        }

        if ($limit)
        {
            $this->limit($limit);
        }

        $key_column = $this->object_column($key);

        $models = $this->where($key_column, 'IS NOT', NULL)->group_by($key_column)->find_all();

        return $models->count() ? $models->as_array() : [];
    }

    public function get_validation_exception_errors(ORM_Validation_Exception $e)
    {
        return $e->errors('models');
    }

    /**
     *
     * This method allows inheritor to preset linked model in URL parameters
     * It is executed after successful url dispatching
     *
     * @param UrlParameters $parameters
     *
     * @return void
     */
    public function preset_linked_models(UrlParameters $parameters)
    {
        // Nothing by default
    }

    /**
     * Returns custom key which may be used for storing model in UrlParameters registry.
     * Default policy applies if NULL returned.
     *
     * @return string|null
     */
    public function get_custom_url_parameters_key()
    {
        // Nothing by default
        return NULL;
    }

    /**
     * Returns string identifier of current DataSource item
     *
     * @return string
     */
    public function get_url_item_id()
    {
        return $this->get_id();
    }

    /**
     * @return $this[]|array
     * @throws Kohana_Exception
     */
    public function get_all()
    {
        return $this->find_all()->as_array();
    }

    /**
     * @param $page
     * @param $itemsPerPage
     * @return \BetaKiller\Search\Model\Results
     */
    public function getSearchResults($page, $itemsPerPage = null)
    {
        // Оборачиваем в пэйджинатор
        $pager = $this->paginateHelper($page, $itemsPerPage);

        // Получаем результаты поиска
        $items = $pager->getResults();

        // Оборачиваем в контейнер
        $results = \BetaKiller\Search\Results::factory(
            $pager->getTotalItems(),
            $pager->getTotalPages(),
            $pager->hasNextPage()
        );

        // Добавляем элементы
        foreach ($items as $item) {
            $results->addItem($item);
        }

        return $results;
    }

    /**
     * @return array
     */
    public function getSearchResultsItemData()
    {
        return $this->getApiResponseData();
    }

    /**
     * @param int       $currentPage
     * @param int|null  $itemsPerPage
     * @return \ORM\PaginateHelper
     */
    public function paginateHelper($currentPage, $itemsPerPage = null)
    {
        return \ORM\PaginateHelper::create(
            $this,
            $currentPage,
            $itemsPerPage ?: 25
        );
    }

}
