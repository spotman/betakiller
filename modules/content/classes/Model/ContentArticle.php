<?php

use BetaKiller\Content\IFace\Article\Item;

class Model_ContentArticle extends ORM
{
    use Model_ORM_ImportedFromWordpressTrait;

    const URL_PARAM = 'ContentArticle';

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
                'through'       =>  'articles_thumbnails',
            ]
        ]);

        $this->load_with([
            'category',
        ]);

        parent::_initialize();
    }

    /**
     * Marker for "updated_at" field change
     * Using this because of ORM::set() is checking value is really changed, but we may set the equal value
     *
     * @var bool
     */
    protected $updated_at_was_set = FALSE;

    /**
     * @param string $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_uri($value)
    {
        return $this->set('uri', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_uri()
    {
        return $this->get('uri');
    }

    public function get_public_url()
    {
        /** @var Item $iface */
        $iface = $this->iface_from_codename('Article\\Item');

        $params = $this->url_parameters_instance()->set(self::URL_PARAM, $this);

        return $iface->url($params);
    }

    /**
     * @param string $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_label($value)
    {
        return $this->set('label', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_label()
    {
        return $this->get('label');
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
     * @param string $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_content($value)
    {
        return $this->set('content', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_content()
    {
        return $this->get('content');
    }

    /**
     * @param string $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_title($value)
    {
        return $this->set('title', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_title()
    {
        return $this->get('title');
    }

    /**
     * @param string $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_description($value)
    {
        return $this->set('description', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_description()
    {
        return $this->get('description');
    }

    /**
     * @param DateTime $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_created_at(DateTime $value)
    {
        return $this->set('created_at', $value->format('Y-m-d H:i:s'));
    }

    /**
     * @return DateTime|null
     * @throws Kohana_Exception
     */
    public function get_created_at()
    {
        $value = $this->get('created_at');

        return $value ? new DateTime($value) : NULL;
    }

    /**
     * @param DateTime $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_updated_at(DateTime $value)
    {
        $this->updated_at_was_set = TRUE;

        return $this->set('updated_at', $value->format('Y-m-d H:i:s'));
    }

    /**
     * @return DateTime|null
     * @throws Kohana_Exception
     */
    public function get_updated_at()
    {
        $value = $this->get('updated_at');

        return $value ? new DateTime($value) : NULL;
    }

    /**
     * @return DateTime
     */
    public function get_last_modified()
    {
        return $this->get_updated_at() ?: $this->get_created_at();
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
     * Insert a new object to the database
     * @param  Validation $validation Validation object
     * @throws Kohana_Exception
     * @return ORM
     */
    public function create(Validation $validation = NULL)
    {
        $this->set_created_at(new DateTime);

        return parent::create($validation);
    }

    /**
     * Updates a single record or multiple records
     *
     * @chainable
     * @param  Validation $validation Validation object
     * @throws Kohana_Exception
     * @return ORM
     */
    public function update(Validation $validation = NULL)
    {
        if ($this->changed() AND !$this->updated_at_was_set)
        {
            $this->set_updated_at(new DateTime);
        }

        return parent::update($validation);
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
     * @param string $value
     *
     * @return $this
     */
    public function filter_uri($value)
    {
        return $this->where($this->object_column('uri'), '=', $value);
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
