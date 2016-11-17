<?php
use BetaKiller\Content\IFace\Article\Item;

class Model_ContentArticle extends Model_ORM_ContentBase
{
    use Model_ORM_ImportedFromWordpressTrait;

    const URL_PARAM = 'ContentArticle';

    protected $_table_name = 'content_articles';

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function _initialize()
    {
        $this->belongs_to([
            'category'          =>  [
                'model'         =>  'ContentCategory',
                'foreign_key'   =>  'category_id',
            ],
        ]);

        $this->has_many([
            'thumbnails'        =>  [
                'model'         =>  'ContentImageElement',
                'foreign_key'   =>  'article_id',
                'far_key'       =>  'content_image_id',
                'through'       =>  'content_articles_thumbnails',
            ]
        ]);

        $this->load_with([
            'category',
        ]);

        parent::_initialize();
    }

    public function get_public_url()
    {
        /** @var Item $iface */
        $iface = $this->iface_from_codename('Article\\Item');

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
