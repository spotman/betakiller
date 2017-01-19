<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use BetaKiller\Utils;
use BetaKiller\Search\Model\Applicable;
use BetaKiller\Search\Model\ResultsItem;

class ORM extends Utils\Kohana\ORM implements OrmInterface, API_Response_Item, URL_DataSourceInterface, Applicable, ResultsItem
{
    /**
     * @param string $model
     * @param null   $id
     *
     * @return OrmInterface
     */
    public static function factory($model, $id = NULL)
    {
        // Set class name
        $class_name = 'Model_'.$model;

        // TODO Create one basic app-namespaced factory and use it in ORM, IFaceFactory, WidgetFactory, etc

        $object = \BetaKiller\DI\Container::instance()->make($class_name, ['id' => $id]);

        return $object;
    }

    /**
     * Default implementation for ORM objects
     * Override this method in child classes
     *
     * @return array
     */
    public function get_api_response_data()
    {
        return $this->as_array();
    }

    /**
     * Default implementation for ORM objects
     * Override this method in child classes
     *
     * @return DateTime|NULL
     */
    public function get_api_last_modified()
    {
        // Empty by default
        return NULL;
    }

    /**
     * Performs search for model item where the $key property value is equal to $value
     *
     * @param string $key
     * @param string $value
     * @param URL_Parameters $parameters
     *
     * @return URL_DataSourceInterface|NULL
     */
    public function find_by_url_key($key, $value, URL_Parameters $parameters)
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
     * @param URL_Parameters $parameters
     */
    protected function custom_find_by_url_filter(URL_Parameters $parameters)
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
     * @param string         $key
     * @param URL_Parameters $parameters
     * @param int|null       $limit
     *
     * @return \URL_DataSourceInterface[]
     */
    public function get_available_items_by_url_key($key, URL_Parameters $parameters, $limit = NULL)
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
     * @param URL_Parameters $parameters
     *
     * @return void
     */
    public function preset_linked_models(URL_Parameters $parameters)
    {
        // Nothing by default
    }

    /**
     * Returns custom key which may be used for storing model in URL_Parameters registry.
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
     * @return \Database_Result|\$this[]
     * @throws Kohana_Exception
     */
    public function get_all()
    {
        return $this->find_all();
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
        return $this->get_api_response_data();
    }

    /**
     * @param int       $currentPage
     * @param int|null  $itemsPerPage
     * @return \ORM\PaginateHelper
     */
    public function paginateHelper($currentPage, $itemsPerPage = null)
    {
        return \ORM\PaginateHelper::factory(
            $this,
            $currentPage,
            $itemsPerPage ?: 25
        );
    }

}
