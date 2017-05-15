<?php

use BetaKiller\Model\ModelWithRevisionsInterface;
use BetaKiller\Model\ModelWithRevisionsOrmTrait;
use BetaKiller\Model\UserInterface;
use BetaKiller\Content\ImportedFromWordpressInterface;
use BetaKiller\Content\LinkedContentModelInterface;
use BetaKiller\Content\Shortcode;
use BetaKiller\Helper\IFaceTrait;
use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\IFace\Url\UrlParametersInterface;
use BetaKiller\Status\StatusRelatedModelOrmTrait;
use BetaKiller\Status\StatusRelatedModelInterface;
use Spotman\Api\AbstractCrudMethodsModelInterface;

class Model_ContentPost extends \ORM implements StatusRelatedModelInterface, ModelWithRevisionsInterface, SeoMetaInterface, ImportedFromWordpressInterface, LinkedContentModelInterface, AbstractCrudMethodsModelInterface
{
    use StatusRelatedModelOrmTrait,
        ModelWithRevisionsOrmTrait,
        Model_ORM_SeoContentTrait,
        Model_ORM_ImportedFromWordpressTrait,
        IFaceTrait {
        StatusRelatedModelOrmTrait::workflow as private baseWorkflow;
    }

    const URL_PARAM = 'ContentPost';

    const TYPE_ARTICLE = 1;
    const TYPE_PAGE = 2;

    protected $prioritizedTypesList = [
        self::TYPE_PAGE,
        self::TYPE_ARTICLE,
    ];

    protected static $updatedAtMarkers = [
        'uri',
    ];

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function _initialize()
    {
        $this->_table_name = 'content_posts';

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

        $this->initializeRevisionsRelations();

        $this->initialize_related_model_relation();

        parent::_initialize();
    }

    /**
     * Rule definitions for validation
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type'   =>  [
                ['not_empty'],
            ],
            'created_by'   =>  [
                ['not_empty'],
            ],
            'status_id'   =>  [
                ['not_empty'],
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getRevisionModelName()
    {
        return 'ContentPostRevision';
    }

    /**
     * @return string
     */
    protected function getRelatedModelRevisionForeignKey()
    {
        return 'revision_id';
    }

    /**
     * @return string
     */
    protected function getRevisionModelForeignKey()
    {
        return 'post_id';
    }

    /**
     * @return string[]
     */
    protected function getFieldsWithRevisions()
    {
        return [
            'label',
            'content',
            'title',
            'description',
        ];
    }

    /**
     * Returns key for workflow factory
     *
     * @return string
     */
    protected function get_workflow_name()
    {
        return 'ContentPost';
    }

    /**
     * @return \Status_Workflow_ContentPost|\BetaKiller\Status\StatusWorkflowInterface
     */
    protected function workflow()
    {
        return $this->baseWorkflow();
    }

    /**
     * @return string
     */
    protected function get_status_relation_model_name()
    {
        return 'ContentPostStatus';
    }

    /**
     * @return string
     */
    protected function get_status_relation_foreign_key()
    {
        return 'status_id';
    }

    /**
     * @return \Model_ContentPost
     */
    public function draft()
    {
        $this->workflow()->draft();
        return $this;
    }

    /**
     * @return \Model_ContentPost
     */
    public function complete()
    {
        $this->workflow()->complete();
        return $this;
    }

    /**
     * @return Model_ContentPost
     */
    public function publish()
    {
        $this->workflow()->publish();
        return $this;
    }

    public function pause()
    {
        $this->workflow()->pause();
        return $this;
    }

    public function fix()
    {
        $this->workflow()->fix();
        return $this;
    }

    /**
     * Returns custom key which may be used for storing model in UrlParameters registry.
     * Default policy applies if NULL returned.
     *
     * @return string|null
     */
    public function getCustomUrlParametersKey()
    {
        // Store all child models under this key
        return self::URL_PARAM;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->get('type');
    }

    protected function setType($value)
    {
        if (!in_array($value, $this->prioritizedTypesList, true)) {
            throw new Kohana_Exception('Post type :value is not allowed', [':value' => $value]);
        }

        return $this->set('type', $value);
    }

    public function markAsPage()
    {
        return $this->setType(self::TYPE_PAGE);
    }

    public function markAsArticle()
    {
        return $this->setType(self::TYPE_ARTICLE);
    }

    public function isPage()
    {
        return ($this->getType() === self::TYPE_PAGE);
    }

    public function is_article()
    {
        return ($this->getType() === self::TYPE_ARTICLE);
    }

    /**
     * @param Model_ContentCategory $value
     *
     * @return $this
     * @throws Kohana_Exception
     */
    public function setCategory(Model_ContentCategory $value)
    {
        return $this->set('category', $value);
    }

    /**
     * @return Model_ContentCategory
     * @throws Kohana_Exception
     */
    public function getCategory()
    {
        return $this->get('category');
    }

    /**
     * @param string $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function setUri($value)
    {
        return $this->set('uri', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function getUri()
    {
        return $this->get('uri');
    }

    /**
     * @param string $value
     * @return \Model_ContentPost
     * @throws Kohana_Exception
     */
    public function setLabel($value)
    {
        return $this->set('label', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function getLabel()
    {
        return $this->get('label');
    }

    /**
     * @param string $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function setContent($value)
    {
        return $this->set('content', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function getContent()
    {
        return $this->get('content');
    }

    public function getContentPreview($length = 250, $end_chars = '...')
    {
        $text = $this->getContent();
        $text = strip_tags($text);
        $text = Shortcode::getInstance()->stripTags($text);

        return Text::limit_chars($text, $length, $end_chars, true);
    }

    /**
     * @param DateTime $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function setCreatedAt(DateTime $value)
    {
        return $this->set_datetime_column_value('created_at', $value);
    }

    /**
     * @return DateTime|null
     * @throws Kohana_Exception
     */
    public function getCreatedAt()
    {
        return $this->get_datetime_column_value('created_at');
    }

    /**
     * @param DateTime $value
     * @return $this
     * @throws Kohana_Exception
     */
    public function setUpdatedAt(DateTime $value)
    {
        return $this->set_datetime_column_value('updated_at', $value);
    }

    /**
     * @return DateTime|null
     * @throws Kohana_Exception
     */
    public function getUpdatedAt()
    {
        return $this->get_datetime_column_value('updated_at');
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return $this
     */
    public function setCreatedBy(UserInterface $user)
    {
        return $this->set('created_by', $user);
    }

    /**
     * @return UserInterface
     */
    public function getCreatedBy()
    {
        return $this->get('created_by');
    }

    /**
     * @return DateTime
     */
    public function getApiLastModified()
    {
        return $this->getUpdatedAt() ?: $this->getCreatedAt();
    }

    /**
     * @return $this
     */
    public function incrementViewsCount()
    {
        $current = $this->getViewsCount();

        return $this->setViewsCount(++$current);
    }

    /**
     * @return int
     * @throws Kohana_Exception
     */
    public function getViewsCount()
    {
        return (int) $this->get('views_count');
    }

    /**
     * @param int $value
     * @return $this
     * @throws Kohana_Exception
     */
    protected function setViewsCount($value)
    {
        return $this->set('views_count', (int) $value);
    }

    public function orderByViewsCount($asc = false)
    {
        return $this->order_by('views_count', $asc ? 'ASC' : 'DESC');
    }

    public function getPopularArticles($limit = 5, $exclude_id = NULL)
    {
        return $this->getPopularContent(self::TYPE_ARTICLE, $limit, $exclude_id);
    }

    public function getFreshArticles($limit = 5, $exclude_id = NULL)
    {
        return $this->getFreshContent(self::TYPE_ARTICLE, $limit, $exclude_id);
    }

    /**
     * @param int|null $limit
     *
     * @return Model_ContentPost[]|\Database_Result
     */
    public function getAllArticles($limit = null)
    {
        if ($limit) {
            $this->limit($limit);
        }

        return $this->filterArticles()->get_all();
    }

    /**
     * @return $this[]|\Database_Result
     */
    public function getAllPages()
    {
        return $this->filterPages()->get_all();
    }

    /**
     * @param int|int[]|null $filter_type
     * @param int $limit
     * @param int|int[]|null $exclude_id
     *
     * @return $this[]
     */
    protected function getPopularContent($filter_type, $limit = 5, $exclude_id = NULL)
    {
        /** @var \Model_ContentPost $model */
        $model = $this->model_factory();

        if ($exclude_id)
        {
            $model->filter_ids((array) $exclude_id, TRUE);
        }

        $model->filterTypes((array) $filter_type);

        return $model->orderByViewsCount()->limit($limit)->get_all();
    }

    /**
     * @param int|int[]|null $filter_type
     * @param int $limit
     * @param int|int[]|null $exclude_id
     *
     * @return $this[]
     */
    protected function getFreshContent($filter_type, $limit = 5, $exclude_id = NULL)
    {
        /** @var \Model_ContentPost $model */
        $model = $this->model_factory();

        if ($exclude_id)
        {
            $model->filter_ids((array) $exclude_id, TRUE);
        }

        $model->filterTypes((array) $filter_type);

        return $model->orderByCreatedAt()->limit($limit)->get_all();
    }

    /**
     * @return \ORM
     */
    protected function getThumbnailsRelation()
    {
        return $this->get('thumbnails');
    }

    /**
     * @return Model_ContentPostThumbnail[]
     */
    public function getThumbnails()
    {
        return $this->getThumbnailsQuery()->get_all();
    }

    /**
     * @return Model_ContentPostThumbnail
     */
    public function getFirstThumbnail()
    {
        return $this->getThumbnailsQuery()->limit(1)->find();
    }

    /**
     * @return Model_ContentPostThumbnail|\ORM|Database_Query_Builder_Select
     */
    protected function getThumbnailsQuery()
    {
        return $this->getThumbnailsRelation()->order_by('place', 'ASC');
    }

    /**
     *
     * This method allows inheritor to preset linked model in URL parameters
     * It is executed after successful url dispatching
     *
     * @param UrlParametersInterface $parameters
     *
     * @return void
     */
    public function presetLinkedModels(UrlParametersInterface $parameters)
    {
        $category = $this->getCategory();

        if ($category->get_id() && !$parameters->get($category::URL_PARAM))
        {
            $parameters->set($category::URL_PARAM, $category);
        }
    }

    public function isDefault()
    {
        return ($this->getUri() === $this->getDefaultUrlValue());
    }

    public function filterDefault()
    {
        return $this->where('uri', '!=', $this->getDefaultUrlValue());
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function filterCategoryIDs(array $ids)
    {
        return $this->where($this->object_column('category_id'), 'IN', $ids);
    }

    /**
     * @param Model_ContentCategory $category
     *
     * @return $this
     */
    public function filterCategory(Model_ContentCategory $category = NULL)
    {
        $column = $this->object_column('category_id');

        return $category
            ? $this->where($column, '=', $category->get_id())
            : $this->where($column, 'IS', NULL);
    }

    /**
     * @return $this
     */
    public function filterWithCategory()
    {
        return $this->where($this->object_column('category_id'), 'IS NOT', NULL);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function filterUri($value)
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
        $this
            ->setCreatedAt(new DateTime)
            ->set_start_status();

        $result = parent::create($validation);

        $this->createRevisionRelatedModel();

        return $result;
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
        $changed = array_intersect($this->_changed, self::$updatedAtMarkers) && !$this->changed('updated_at');

        if ($changed || $this->isRevisionDataChanged()) {
            $this->setUpdatedAt(new DateTime);
        }

        $this->updateRevisionRelatedModel();

        return parent::update($validation);
    }

    public function prioritizeByPostTypes()
    {
        return $this->orderByPostTypes($this->prioritizedTypesList);
    }

    public function orderByPostTypes(array $values)
    {
        return $this->order_by_field_sequence('type', $values);
    }

    public function orderByCreatedAt($asc = false)
    {
        return $this->order_by('created_at', $asc ? 'ASC' : 'DESC');
    }

    public function filterType($value)
    {
        return $this->where('type', '=', $value);
    }

    public function filterTypes(array $values)
    {
        return $this->where('type', 'IN', $values);
    }

    public function filterArticles()
    {
        return $this->filterType(self::TYPE_ARTICLE);
    }

    public function filterPages()
    {
        return $this->filterType(self::TYPE_PAGE);
    }

    public function filterCreatedBy(DateTime $date, $op = '<')
    {
        return $this->filter_datetime_column_value('created_at', $date, $op);
    }

    public function filterPostsBefore(DateTime $date)
    {
        return $this->filterCreatedBy($date, '<')->orderByCreatedAt();
    }

    public function search($term)
    {
        return $this->search_query($term, [
            $this->object_column('label'),
            $this->object_column('content')
        ]);
    }

    /**
     * @param UrlParametersInterface $parameters
     */
    protected function custom_find_by_url_filter(UrlParametersInterface $parameters)
    {
        // Load pages first
        $this->prioritizeByPostTypes();

        $category = $parameters->get(Model_ContentCategory::URL_PARAM);

        $this->filterUserAllowedStatuses();

        // Show only posts having actual revision
        $this->filterHavingActualRevision();

        $this->and_where_open();

        // Plain pages
        $this->or_where_open()
            ->filterType(self::TYPE_PAGE)
            ->filterCategory(NULL) // Pages have no category
            ->or_where_close();

        // Articles
        $this->or_where_open()->filterType(self::TYPE_ARTICLE);

        if ($category)
        {
            // Concrete category
            $this->filterCategory($category);
        }
        else
        {
            // Any category (articles must have category)
            $this->filterWithCategory();
        }

        $this->or_where_close();

        $this->and_where_close();
    }

    private function filterPublished()
    {
        return $this->filter_status_id(Model_ContentPostStatus::PUBLISHED_ID);
    }

    public function filterUserAllowedStatuses()
    {
        $user = $this->current_user(TRUE);

        // Allow get_moderator_role find all statuses
        // TODO Statuses ACL instead of this ugly thing
        if ($user && ($user->is_moderator() || $user->is_developer() || $user->has_role('content'))) {
            return $this;
        }

        // Only published posts must be displayed
        return $this->filterPublished();
    }

    public function get_public_url()
    {
        /** @var \BetaKiller\IFace\App\Content\PostItem $iface */
        $iface = $this->iface_from_codename('App_Content_PostItem');

        $params = $this->url_parameters_instance()->set(self::URL_PARAM, $this);

        if ($this->isDefault()) {
            $this->setUri('/'); // Nullify uri so URL must not have default "index" string
        }

        $this->presetLinkedModels($params);

        return $iface->url($params);
    }

    // TODO Move this method to base class and detect IFace via model-iface linking
    public function get_admin_url()
    {
        /** @var \BetaKiller\IFace\Admin\Content\PostItem $iface */
        $iface = $this->iface_from_codename('Admin_Content_PostItem');

        $params = $this->url_parameters_instance()->set(self::URL_PARAM, $this);

        $this->presetLinkedModels($params);

        return $iface->url($params);
    }
}
