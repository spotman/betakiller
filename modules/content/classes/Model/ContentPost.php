<?php

use BetaKiller\Content\ImportedFromWordpressInterface;
use BetaKiller\Content\SeoContentInterface;

class Model_ContentPost extends ORM implements SeoContentInterface, ImportedFromWordpressInterface
{
    use Model_ORM_SeoContentTrait,
        Model_ORM_ImportedFromWordpressTrait;

    const URL_PARAM = 'ContentPost';

    const TYPE_ARTICLE = 1;
    const TYPE_PAGE = 2;

    protected $_prioritized_types_list = [
        self::TYPE_PAGE,
        self::TYPE_ARTICLE,
    ];

    /**
     * Marker for "updated_at" field change
     * Using this because of ORM::set() is checking value is really changed, but we may set the equal value
     *
     * @var bool
     */
    protected $updated_at_was_set = FALSE;

    protected $_table_name = 'content_posts';

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
                'model'         =>  'ContentPostThumbnail',
                'foreign_key'   =>  'content_post_id',
//                'far_key'       =>  'content_image_id',
//                'through'       =>  'content_posts_thumbnails',
            ]
        ]);

        $this->load_with([
            'category',
        ]);

        parent::_initialize();
    }

    /**
     * Returns custom key which may be used for storing model in URL_Parameters registry.
     * Default policy applies if NULL returned.
     *
     * @return string|null
     */
    public function get_custom_url_parameters_key()
    {
        // Store all child models under this key
        return self::URL_PARAM;
    }

    /**
     * @return string
     */
    public function get_type()
    {
        return $this->get('type');
    }

    public function set_type($value)
    {
        if (!in_array($value, $this->_prioritized_types_list))
            throw new Kohana_Exception('Post type :value is not allowed');

        return $this->set('type', $value);
    }

    public function mark_as_page()
    {
        return $this->set_type(self::TYPE_PAGE);
    }

    public function is_page()
    {
        return ($this->get_type() == self::TYPE_PAGE);
    }

    public function is_article()
    {
        return ($this->get_type() == self::TYPE_ARTICLE);
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
     * @param DateTime $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_created_at(DateTime $value)
    {
        return $this->set_datetime_column_value('created_at', $value);
    }

    /**
     * @return DateTime|null
     * @throws Kohana_Exception
     */
    public function get_created_at()
    {
        return $this->get_datetime_column_value('created_at');
    }

    /**
     * @param DateTime $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function set_updated_at(DateTime $value)
    {
        return $this->set_datetime_column_value('updated_at', $value);
    }

    /**
     * @return DateTime|null
     * @throws Kohana_Exception
     */
    public function get_updated_at()
    {
        return $this->get_datetime_column_value('updated_at');
    }

    /**
     * @return DateTime
     */
    public function get_api_last_modified()
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

    public function order_by_views_count($asc = false)
    {
        return $this->order_by('views_count', $asc ? 'ASC' : 'DESC');
    }

    public function get_popular_articles($limit = 5, $exclude_id = NULL)
    {
        return $this->get_popular_content(self::TYPE_ARTICLE, $limit, $exclude_id);
    }

    /**
     * @param int|null $limit
     *
     * @return Model_ContentPost[]|\Database_Result
     */
    public function get_all_articles($limit = null)
    {
        if ($limit) {
            $this->limit($limit);
        }

        return $this->filter_articles()->get_all();
    }

    /**
     * @return $this[]|\Database_Result
     */
    public function get_all_pages()
    {
        return $this->filter_pages()->get_all();
    }

    /**
     * @param int|int[]|null $filter_type
     * @param int $limit
     * @param int|int[]|null $exclude_id
     *
     * @return \Database_Result|\Model_ContentPost[]
     */
    protected function get_popular_content($filter_type, $limit = 5, $exclude_id = NULL)
    {
        $model = $this->model_factory();

        if ($exclude_id)
        {
            $model->filter_ids((array) $exclude_id, TRUE);
        }

        $model->filter_types((array) $filter_type);

        return $model->order_by_views_count()->limit($limit)->get_all();
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

    /**
     * @return Model_ContentPostThumbnail[]|Database_Result
     */
    public function get_thumbnails()
    {
        return $this->get_thumbnails_query()->get_all();
    }

    /**
     * @return Model_ContentPostThumbnail
     */
    public function get_first_thumbnail()
    {
        return $this->get_thumbnails_query()->limit(1)->find();
    }

    /**
     * @return Model_ContentPostThumbnail|\ORM|Database_Query_Builder_Select
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

        if ($category->get_id() && !$parameters->get($category::URL_PARAM))
        {
            $parameters->set($category::URL_PARAM, $category);
        }
    }

    public function is_default()
    {
        return ($this->get_uri() == $this->get_default_url_value());
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
    public function filter_category(Model_ContentCategory $category = NULL)
    {
        $column = $this->object_column('category_id');

        return $category
            ? $this->where($column, '=', $category->get_id())
            : $this->where($column, 'IS', NULL);
    }

    /**
     * @return $this
     */
    public function filter_with_category()
    {
        return $this->where($this->object_column('category_id'), 'IS NOT', NULL);
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

    public function prioritize_by_post_types()
    {
        return $this->order_by_post_types($this->_prioritized_types_list);
    }

    public function order_by_post_types(array $values)
    {
        return $this->order_by_field_sequence('type', $values);
    }

    public function order_by_created_at($desc = false)
    {
        return $this->order_by('created_at', $desc ? 'DESC' : 'ASC');
    }

    public function filter_type($value)
    {
        return $this->where('type', '=', $value);
    }

    public function filter_types(array $values)
    {
        return $this->where('type', 'IN', $values);
    }

    public function filter_articles()
    {
        return $this->filter_type(self::TYPE_ARTICLE);
    }

    public function filter_pages()
    {
        return $this->filter_type(self::TYPE_PAGE);
    }

    public function filter_created_by(DateTime $date, $op = '<')
    {
        return $this->filter_datetime_column_value('created_at', $date, $op);
    }

    public function filter_posts_before(DateTime $date)
    {
        return $this->filter_created_by($date, '<')->order_by_created_at(true);
    }

    /**
     * @param URL_Parameters $parameters
     */
    protected function custom_find_by_url_filter(URL_Parameters $parameters)
    {
        // Load pages first
        $this->prioritize_by_post_types();

        $category = $parameters->get(Model_ContentCategory::URL_PARAM);

        $this->and_where_open();

        // Plain pages
        $this->or_where_open()
            ->filter_type(self::TYPE_PAGE)
            ->filter_category(NULL) // Pages have no category
            ->or_where_close();

        // Articles
        $this->or_where_open()->filter_type(self::TYPE_ARTICLE);

        if ($category)
        {
            // Concrete category
            $this->filter_category($category);
        }
        else
        {
            // Any category (articles must have category)
            $this->filter_with_category();
        }

        $this->or_where_close();

        $this->and_where_close();
    }

    public function get_public_url()
    {
        /** @var \BetaKiller\Content\IFace\ContentItem $iface */
        $iface = $this->iface_from_codename('ContentItem');

        $params = $this->url_parameters_instance()->set(self::URL_PARAM, $this);

        if ($this->is_default()) {
            $this->set_uri('/'); // Nullify uri so URL must not have default "index" string
        }

        $this->preset_linked_models($params);

        return $iface->url($params);
    }

    // TODO Move this method to base class and detect IFace via model-iface linking
    public function get_admin_url()
    {
        /** @var \BetaKiller\Content\IFace\Admin\PostItem $iface */
        $iface = $this->iface_from_codename('Admin_Content_ArticleItem');

        $params = $this->url_parameters_instance()->set(self::URL_PARAM, $this);

        $this->preset_linked_models($params);

        return $iface->url($params);
    }
}
