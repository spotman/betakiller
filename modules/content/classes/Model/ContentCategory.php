<?php

use BetaKiller\Content\ImportedFromWordpressInterface;
use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\IFace\Url\UrlParametersInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentOrm;

class Model_ContentCategory extends TreeModelSingleParentOrm implements SeoMetaInterface, ImportedFromWordpressInterface
{
    use Model_ORM_ImportedFromWordpressTrait,
        Model_ORM_SeoContentTrait;

    protected function _initialize()
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
     *
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

    public function is_active()
    {
        return (bool)$this->get('is_active');
    }

    /**
     * @param bool $include_self
     *
     * @return array|\int[]
     */
    public function get_all_related_categories_ids($include_self = true)
    {
        // Collect all children categories
        $ids = $this->getAllChildren($this->primary_key());

        if ($include_self) {
            // Add current category
            $ids[] = $this->get_id();
        }

        // Remove empty values
        $ids = array_filter($ids);

        return $ids;
    }

    /**
     * @return Model_ContentPost[]
     */
    public function get_related_articles()
    {
        return $this->get_posts_relation()->get_all();
    }

    public function filter_is_active(?bool $value = null)
    {
        return $this->where($this->object_column('is_active'), '=', $value ?? true);
    }

    public function order_by_place(?bool $desc = null)
    {
        return $this->order_by($this->object_column('place'), ($desc ?? false ) ? 'desc' : 'asc');
    }

    /**
     * Place here additional query params
     *
     * @return $this
     */
    protected function additionalTreeModelFiltering()
    {
        return $this->filter_is_active()->order_by_place();
    }

    public function link_posts(array $item_ids)
    {
        return $this->link_related('posts', $item_ids);
    }

    /**
     * @return \Model_ContentPost
     */
    protected function get_posts_relation()
    {
        return $this->get('posts');
    }

    /**
     * @param UrlParametersInterface $parameters
     */
    protected function customFilterForUrlDispatching(UrlParametersInterface $parameters): void
    {
        $parent_category = $parameters->getEntityByClassName($this);

        $this->filter_is_active()->filter_parent($parent_category);
    }
}
