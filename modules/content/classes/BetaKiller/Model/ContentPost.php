<?php
namespace BetaKiller\Model;

use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\IFace\Url\UrlContainerInterface;
use BetaKiller\IFace\Url\UrlDispatcher;
use BetaKiller\Status\StatusRelatedModelInterface;
use BetaKiller\Status\StatusRelatedModelOrmTrait;
use Database_Query_Builder_Select;
use DateTime;
use Kohana_Exception;
use ORM;
use Validation;

class ContentPost extends \ORM implements StatusRelatedModelInterface, ModelWithRevisionsInterface, SeoMetaInterface, EntityHasWordpressIdInterface, HasPublicZoneAccessSpecificationInterface
{
    use StatusRelatedModelOrmTrait,
        ModelWithRevisionsOrmTrait,
        OrmBasedSeoMetaTrait,
        OrmBasedEntityHasWordpressIdTrait {
        StatusRelatedModelOrmTrait::workflow as private baseWorkflow;
    }

    const TYPE_ARTICLE = 1;
    const TYPE_PAGE    = 2;

    private static $prioritizedTypesList = [
        self::TYPE_PAGE,
        self::TYPE_ARTICLE,
    ];

    private static $updatedAtMarkers = [
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
     * @return \BetaKiller\Model\ContentPost
     */
    public function draft(): ContentPost
    {
        $this->workflow()->draft();

        return $this;
    }

    /**
     * @return \BetaKiller\Model\ContentPost
     */
    public function complete(): ContentPost
    {
        $this->workflow()->complete();

        return $this;
    }

    /**
     * @return \BetaKiller\Model\ContentPost
     */
    public function publish(): ContentPost
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
        if (!in_array($value, self::$prioritizedTypesList, true)) {
            throw new Kohana_Exception('Post type :value is not allowed', [':value' => $value]);
        }

        return $this->set('type', $value);
    }

    public static function getPrioritizedTypesList(): array
    {
        return self::$prioritizedTypesList;
    }

    public function markAsPage(): ContentPost
    {
        return $this->setType(self::TYPE_PAGE);
    }

    public function markAsArticle(): ContentPost
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
     * @param ContentCategory $value
     *
     * @throws Kohana_Exception
     */
    public function setCategory(ContentCategory $value): void
    {
        $this->set('category', $value);
    }

    /**
     * @return ContentCategory
     * @throws Kohana_Exception
     */
    public function getCategory(): ContentCategory
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
     * @throws Kohana_Exception
     */
    public function setLabel(string $value): void
    {
        $this->set('label', $value);
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
     * @return \DateTimeImmutable
     * @throws Kohana_Exception
     */
    public function getCreatedAt(): \DateTimeImmutable
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
     * @return \DateTimeImmutable|null
     * @throws Kohana_Exception
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
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
    public function getCreatedBy(): UserInterface
    {
        return $this->get('created_by');
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getApiLastModified(): \DateTimeImmutable
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

    /**
     * @return \ORM
     */
    protected function getThumbnailsRelation(): \ORM
    {
        return $this->get('thumbnails');
    }

    /**
     * @return \BetaKiller\Model\ContentPostThumbnailInterface[]
     */
    public function getThumbnails(): array
    {
        return $this->getThumbnailsQuery()->get_all();
    }

    /**
     * @return \BetaKiller\Model\ContentPostThumbnailInterface
     */
    public function getFirstThumbnail(): ContentPostThumbnailInterface
    {
        return $this->getThumbnailsQuery()->limit(1)->find();
    }

    /**
     * @return \BetaKiller\Model\ContentPostThumbnail|\ORM|Database_Query_Builder_Select
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
     * @param UrlContainerInterface $parameters
     *
     * @return void
     * @deprecated
     */
    public function presetLinkedEntities(UrlContainerInterface $parameters): void
    {
        $category = $this->getCategory();

        if ($category->get_id() && !$parameters->getEntityByClassName($category)) {
            $parameters->setParameter($category);
        }
    }

    public function isDefault(): bool
    {
        return ($this->getUri() === UrlDispatcher::DEFAULT_URI);
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

    public function isPublicZoneAccessAllowed(): bool
    {
        // Show only posts having actual revision
        return $this->hasActualRevision();
    }
}
