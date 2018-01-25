<?php
namespace BetaKiller\Model;

use Kohana_Exception;

class ContentCategory extends AbstractOrmBasedSingleParentTreeModel implements ContentCategoryInterface
{
    use OrmBasedEntityHasWordpressIdTrait,
        OrmBasedSeoMetaTrait;

    protected function _initialize(): void
    {
        $this->_table_name = 'content_categories';

        $this->has_many([
            'posts' => [
                'model'       => 'ContentPost',
                'foreign_key' => 'category_id',
            ],
        ]);

        parent::_initialize();
    }

    /**
     * @param string $value
     *
     * @throws Kohana_Exception
     */
    public function setUri(string $value): void
    {
        $this->set('uri', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function getUri(): string
    {
        return (string)$this->get('uri');
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
        return (string)$this->get('label');
    }

    public function isActive(): bool
    {
        return (bool)$this->get('is_active');
    }

    public function linkPosts(array $item_ids): void
    {
        $this->link_related('posts', $item_ids);
    }

    /**
     * @return \BetaKiller\Model\ContentPost
     */
    protected function getPostsRelation(): ContentPost
    {
        return $this->get('posts');
    }
}
