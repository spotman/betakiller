<?php
namespace BetaKiller\Model;

use BetaKiller\Utils\Kohana\TreeModelSingleParentOrm;
use Kohana_Exception;

class ContentCategory extends TreeModelSingleParentOrm implements ContentCategoryInterface
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

    /**
     * @param bool|null $include_self
     *
     * @return array|\int[]
     * @deprecated
     * @todo Move to repository
     */
    public function get_all_related_categories_ids(?bool $include_self = null): array
    {
        // Collect all children categories
        $ids = $this->getAllChildren($this->primary_key());

        if ($include_self ?? true) {
            // Add current category
            $ids[] = $this->get_id();
        }

        // Remove empty values
        $ids = array_filter($ids);

        return $ids;
    }

    /**
     * @param bool|null $value
     *
     * @return $this
     * @deprecated
     * @todo Move to repository
     */
    public function filter_is_active(?bool $value = null)
    {
        return $this->where($this->object_column('is_active'), '=', $value ?? true);
    }

    /**
     * @param bool|null $desc
     *
     * @return $this
     * @deprecated
     * @todo Move to repository
     */
    public function order_by_place(?bool $desc = null)
    {
        return $this->order_by($this->object_column('place'), ($desc ?? false) ? 'desc' : 'asc');
    }

    /**
     * Place here additional query params
     *
     * @return $this
     * @todo Rewrite this to tree model repository
     */
    protected function additionalTreeTraversalFiltering()
    {
        return $this->filter_is_active()->order_by_place();
    }

    public function linkPosts(array $item_ids): void
    {
        $this->link_related('posts', $item_ids);
    }

    /**
     * @return \BetaKiller\Model\ContentPost
     */
    protected function get_posts_relation()
    {
        return $this->get('posts');
    }
}
