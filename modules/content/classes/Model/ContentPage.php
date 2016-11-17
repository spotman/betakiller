<?php
use BetaKiller\Content\IFace\Article\Item;

class Model_ContentPage extends Model_ORM_ContentBase
{
    use Model_ORM_ImportedFromWordpressTrait;

    const URL_PARAM = 'ContentPage';

    protected $_table_name = 'content_pages';

    public function get_public_url()
    {
        /** @var Item $iface */
        $iface = $this->iface_from_codename('Page\\Item');

        $params = $this->url_parameters_instance()->set(self::URL_PARAM, $this);

        return $iface->url($params);
    }

    /**
     * @param Model_ContentCategory $value
     *
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_category(Model_ContentCategory $value)
    {
        return $this->set('category', $value);
    }

    /**
     * @return Model_ContentCategory
     * @throws Kohana_Exception
     */
    public function get_category()
    {
        return $this->get('category');
    }

    /**
     * @return $this
     */
    public function increment_views_count()
    {
        $current = $this->get_views_count();

        return $this->set_views_count(++$current);
    }

    /**
     * @return int
     * @throws Kohana_Exception
     */
    public function get_views_count()
    {
        return (int) $this->get('views_count');
    }

    /**
     * @param int $value
     * @return $this
     * @throws Kohana_Exception
     */
    protected function set_views_count($value)
    {
        return $this->set('views_count', (int) $value);
    }

    /**
     * @param int $wp_id
     * @return $this
     * @throws Kohana_Exception
     */
    public function find_by_wp_id($wp_id)
    {
        $model = $this
            ->model_factory()
            ->filter_wp_id($wp_id)
            ->find();

        if (!$model->loaded())
        {
            $model->clear();
        }

        return $model;
    }

    /**
     * @return \ORM
     */
    protected function get_thumbnails_relation()
    {
        return $this->get('thumbnails');
    }

    public function reset_thumbnails(array $images_ids)
    {
        return $this
            ->remove('thumbnails')
            ->add('thumbnails', $images_ids);
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function filter_category_ids(array $ids)
    {
        return $this->where($this->object_column('category_id'), 'IN', $ids);
    }

    /**
     * @param Model_ContentCategory $category
     *
     * @return $this
     */
    public function filter_category(Model_ContentCategory $category)
    {
        return $this->where($this->object_column('category_id'), '=', $category->get_id());
    }

    /**
     * @param URL_Parameters $parameters
     */
    protected function custom_find_by_url_filter(URL_Parameters $parameters)
    {
        $category = $parameters->get(Model_ContentCategory::URL_PARAM);

        if ($category) {
            $this->filter_category($category);
        }
    }

    public function order_by_views_count($asc = false)
    {
        return $this->order_by('views_count', $asc ? 'ASC' : 'DESC');
    }

    /**
     * @return Model_ContentImageElement[]|Database_Result
     */
    public function get_thumbnails()
    {
        return $this->get_thumbnails_query()->get_all();
    }

    /**
     * @return Model_ContentImageElement
     */
    public function get_first_thumbnail()
    {
        return $this->get_thumbnails_query()->limit(1)->find();
    }

    /**
     * @return Model_ContentImageElement|\ORM|Database_Query_Builder_Select
     */
    protected function get_thumbnails_query()
    {
        return $this->get_thumbnails_relation()->order_by('place', 'ASC');
    }

    /**
     * @param int $limit
     *
     * @return \Database_Result|\Model_ContentArticle[]
     */
    public function get_popular_articles($limit = 5)
    {
        return $this->model_factory()->order_by_views_count()->limit($limit)->get_all();
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
        $category = $this->get_category();

        if ($category && !$parameters->get($category::URL_PARAM))
        {
            $parameters->set($category::URL_PARAM, $category);
        }
    }
}
