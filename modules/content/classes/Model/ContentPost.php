<?php

use BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface;
use BetaKiller\Acl\Resource\StatusRelatedEntityAclResourceInterface;
use BetaKiller\Content\ImportedFromWordpressInterface;
use BetaKiller\Content\Shortcode;
use BetaKiller\Exception;
use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\IFace\Url\UrlDispatcher;
use BetaKiller\IFace\Url\UrlParametersInterface;
use BetaKiller\Model\ModelWithRevisionsInterface;
use BetaKiller\Model\ModelWithRevisionsOrmTrait;
use BetaKiller\Model\UserInterface;
use BetaKiller\Status\StatusRelatedModelInterface;
use BetaKiller\Status\StatusRelatedModelOrmTrait;
use BetaKiller\Model\EntityWithPreviewModeInterface;

class Model_ContentPost extends \ORM implements StatusRelatedModelInterface, ModelWithRevisionsInterface, SeoMetaInterface, ImportedFromWordpressInterface, EntityWithPreviewModeInterface
{
    use StatusRelatedModelOrmTrait,
        ModelWithRevisionsOrmTrait,
        Model_ORM_SeoContentTrait,
        Model_ORM_ImportedFromWordpressTrait {
        StatusRelatedModelOrmTrait::workflow as private baseWorkflow;
    }

    const TYPE_ARTICLE = 1;
    const TYPE_PAGE    = 2;

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
    protected function _initialize(): void
    {
        $this->_table_name = 'content_posts';

        $this->belongs_to([
            'category' => [
                'model'       => 'ContentCategory',
                'foreign_key' => 'category_id',
            ],
        ]);

        $this->has_many([
            'thumbnails' => [
                'model'       => 'ContentPostThumbnail',
                'foreign_key' => 'content_post_id',
//                'far_key'       =>  'content_image_id',
//                'through'       =>  'content_posts_thumbnails',
            ],
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
    public function rules(): array
    {
        return [
            'type'       => [
                ['not_empty'],
            ],
            'created_by' => [
                ['not_empty'],
            ],
            'status_id'  => [
                ['not_empty'],
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getRevisionModelName(): string
    {
        return 'ContentPostRevision';
    }

    /**
     * @return string
     */
    protected function getRelatedModelRevisionForeignKey(): string
    {
        return 'revision_id';
    }

    /**
     * @return string
     */
    protected function getRevisionModelForeignKey(): string
    {
        return 'post_id';
    }

    /**
     * @return string[]
     */
    protected function getFieldsWithRevisions(): array
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
    protected function get_workflow_name(): string
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
    protected function get_status_relation_model_name(): string
    {
        return 'ContentPostStatus';
    }

    /**
     * @return string
     */
    protected function get_status_relation_foreign_key(): string
    {
        return 'status_id';
    }

    /**
     * @return \Model_ContentPost
     */
    public function draft(): \Model_ContentPost
    {
        $this->workflow()->draft();

        return $this;
    }

    /**
     * @return \Model_ContentPost
     */
    public function complete(): \Model_ContentPost
    {
        $this->workflow()->complete();

        return $this;
    }

    /**
     * @return Model_ContentPost
     */
    public function publish(): \Model_ContentPost
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
     * @return int
     */
    public function getType(): int
    {
        return (int)$this->get('type');
    }

    /**
     * @param int $value
     *
     * @return $this
     * @throws \Kohana_Exception
     */
    protected function setType($value)
    {
        if (!in_array($value, $this->prioritizedTypesList, true)) {
            throw new Kohana_Exception('Post type :value is not allowed', [':value' => $value]);
        }

        return $this->set('type', $value);
    }

    public function markAsPage(): \Model_ContentPost
    {
        return $this->setType(self::TYPE_PAGE);
    }

    public function markAsArticle(): \Model_ContentPost
    {
        return $this->setType(self::TYPE_ARTICLE);
    }

    public function isPage(): bool
    {
        return $this->isType(self::TYPE_PAGE);
    }

    public function isArticle(): bool
    {
        return $this->isType(self::TYPE_ARTICLE);
    }

    protected function isType($type): bool
    {
        return ($this->getType() === (int)$type);
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
    public function getCategory(): \Model_ContentCategory
    {
        return $this->get('category');
    }

    public function needsCategory(): bool
    {
        return $this->isArticle();
    }

    public function needsThumbnails(): bool
    {
        // Allow plain pages without thumbnails
        return $this->isArticle();
    }

    /**
     * @param string $value
     *
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
    public function getUri(): string
    {
        return $this->get('uri');
    }

    /**
     * @param string $value
     *
     * @return \Model_ContentPost
     * @throws Kohana_Exception
     */
    public function setLabel($value): \Model_ContentPost
    {
        return $this->set('label', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function getLabel(): string
    {
        return $this->get('label');
    }

    /**
     * @param string $value
     *
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
    public function getContent(): string
    {
        return $this->get('content');
    }

    public function getContentPreview(?int $length = null, ?string $end_chars = null): string
    {
        $text = $this->getContent();
        $text = strip_tags($text);
        $text = Shortcode::getInstance()->stripTags($text);

        return Text::limit_chars($text, $length ?? 250, $end_chars ?? '...', true);
    }

    /**
     * @param DateTime $value
     *
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
    public function getCreatedAt(): ?\DateTime
    {
        return $this->get_datetime_column_value('created_at');
    }

    /**
     * @param DateTime $value
     *
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
    public function getUpdatedAt(): ?\DateTime
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
    public function getCreatedBy(): \BetaKiller\Model\UserInterface
    {
        return $this->get('created_by');
    }

    /**
     * @return DateTime
     */
    public function getApiLastModified(): \DateTime
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
    public function getViewsCount(): int
    {
        return (int)$this->get('views_count');
    }

    /**
     * @param int $value
     *
     * @return $this
     * @throws Kohana_Exception
     */
    protected function setViewsCount($value)
    {
        return $this->set('views_count', (int)$value);
    }

    public function orderByViewsCount(?bool $asc = null)
    {
        return $this->order_by('views_count', ($asc ?? false) ? 'ASC' : 'DESC');
    }

    public function getPopularArticles(?int $limit = null, $exclude_id = null): array
    {
        return $this->getPopularContent(self::TYPE_ARTICLE, $limit, $exclude_id);
    }

    public function getFreshArticles(?int $limit = null, $exclude_id = null): array
    {
        return $this->getFreshContent(self::TYPE_ARTICLE, $limit, $exclude_id);
    }

    /**
     * @param int|null $limit
     *
     * @return Model_ContentPost[]
     */
    public function getAllArticles($limit = null): array
    {
        if ($limit) {
            $this->limit($limit);
        }

        return $this->filterArticles()->get_all();
    }

    /**
     * @return Model_ContentPost[]
     */
    public function getAllPages(): array
    {
        return $this->filterPages()->get_all();
    }

    /**
     * @param int|int[]|null $filter_type
     * @param int            $limit
     * @param int|int[]|null $exclude_id
     *
     * @return $this[]
     */
    protected function getPopularContent($filter_type, ?int $limit, $exclude_id = null): array
    {
        /** @var \Model_ContentPost $model */
        $model = $this->model_factory();

        if ($exclude_id) {
            $model->filter_ids((array)$exclude_id, true);
        }

        $model->filterTypes((array)$filter_type);

        return $model->orderByViewsCount()->limit($limit ?? 5)->get_all();
    }

    /**
     * @param int|int[]|null $filter_type
     * @param int            $limit
     * @param int|int[]|null $exclude_id
     *
     * @return $this[]
     */
    protected function getFreshContent($filter_type, ?int $limit = null, $exclude_id = null): array
    {
        /** @var \Model_ContentPost $model */
        $model = $this->model_factory();

        if ($exclude_id) {
            $model->filter_ids((array)$exclude_id, true);
        }

        $model->filterTypes((array)$filter_type);

        return $model->orderByCreatedAt()->limit($limit ?? 5)->get_all();
    }

    /**
     * @return \ORM
     */
    protected function getThumbnailsRelation(): \ORM
    {
        return $this->get('thumbnails');
    }

    /**
     * @return Model_ContentPostThumbnail[]
     */
    public function getThumbnails(): array
    {
        return $this->getThumbnailsQuery()->get_all();
    }

    /**
     * @return Model_ContentPostThumbnail
     */
    public function getFirstThumbnail(): \Model_ContentPostThumbnail
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
    public function presetLinkedEntities(UrlParametersInterface $parameters): void
    {
        $category = $this->getCategory();

        if ($category->get_id() && !$parameters->getEntityByClassName($category)) {
            $parameters->setEntity($category);
        }
    }

    public function isDefault(): bool
    {
        return ($this->getUri() === UrlDispatcher::DEFAULT_URI);
    }

    /**
     * @param array $ids
     *
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
    public function filterCategory(?Model_ContentCategory $category)
    {
        $column = $this->object_column('category_id');

        return $category
            ? $this->where($column, '=', $category->get_id())
            : $this->where($column, 'IS', null);
    }

    /**
     * @return $this
     */
    public function filterWithCategory()
    {
        return $this->where($this->object_column('category_id'), 'IS NOT', null);
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
     *
     * @param  Validation $validation Validation object
     *
     * @throws Kohana_Exception
     * @return ORM
     */
    public function create(Validation $validation = null): \ORM
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
     *
     * @param  Validation $validation Validation object
     *
     * @throws Kohana_Exception
     * @return ORM
     */
    public function update(Validation $validation = null): \ORM
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

    public function orderByCreatedAt(?bool $asc = null)
    {
        return $this->order_by($this->object_column('created_at'), ($asc ?? false) ? 'ASC' : 'DESC');
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

    public function filterCreatedBy(DateTime $date, ?string $op = null)
    {
        return $this->filter_datetime_column_value('created_at', $date, $op ?? '<');
    }

    public function filterPostsBefore(DateTime $date)
    {
        return $this->filterCreatedBy($date, '<')->orderByCreatedAt();
    }

    public function search($term)
    {
        return $this->search_query($term, [
            $this->object_column('label'),
            $this->object_column('content'),
        ]);
    }

    /**
     * @param UrlParametersInterface $parameters
     */
    protected function customFilterForSearchByUrl(UrlParametersInterface $parameters): void
    {
        // Load pages first
        $this->prioritizeByPostTypes();

        $category = $parameters->getEntityByClassName(Model_ContentCategory::class);

        // Show only posts having actual revision
        $this->filterHavingActualRevision();

        $this->and_where_open();

        // Plain pages
        $this->or_where_open()
            ->filterType(self::TYPE_PAGE)
            // Pages have no category
            ->filterCategory(null)
            ->or_where_close();

        // Articles
        $this->or_where_open()->filterType(self::TYPE_ARTICLE);

        if ($category) {
            // Concrete category
            $this->filterCategory($category);
        } else {
            // Any category (articles must have category)
            $this->filterWithCategory();
        }

        $this->or_where_close();

        $this->and_where_close();
    }

    /**
     * @param \BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface $aclResource
     * @param                                                            $action
     */
    protected function securityFilter(EntityRelatedAclResourceInterface $aclResource, $action): void
    {
        if (!($aclResource instanceof StatusRelatedEntityAclResourceInterface)) {
            throw new Exception('Acl resource :name must implement :must for security filter processing', [
                ':name' => $aclResource->getResourceId(),
                ':must' => StatusRelatedEntityAclResourceInterface::class,
            ]);
        }

        $this->filterAllowedStatuses($aclResource);
    }
}
