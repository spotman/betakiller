<?php

use BetaKiller\Content\ImportedFromWordpressInterface;
use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\IFace\Url\UrlParametersInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentOrm;

class Model_ContentCategory extends TreeModelSingleParentOrm implements SeoMetaInterface, ImportedFromWordpressInterface
{
    use BetaKiller\Helper\IFaceHelperTrait;
    use Model_ORM_ImportedFromWordpressTrait,
        Model_ORM_SeoContentTrait;

    const URL_PARAM = 'ContentCategory';

    protected $_table_name = 'content_categories';

    protected function _initialize()
    {
        $this->has_many([
            'posts'             =>  [
                'model'         =>  'ContentPost',
                'foreign_key'   =>  'category_id',
            ]
        ]);

        parent::_initialize();
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

    public function is_active()
    {
        return (bool) $this->get('is_active');
    }

    // TODO Implement complex IFace - DataSource link and move url logic to IFaceHelper
    public function get_public_url()
    {
        /** @var \BetaKiller\IFace\App\Content\CategoryItem $iface */
        $iface = $this->iface_from_codename('App_Content_CategoryItem');

        $params = $this->url_parameters_instance()
            ->setEntity($this);

        return $iface->url($params);
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
     * @return Model_ContentPost[]|\Database_Result
     */
    public function get_related_articles()
    {
        return $this->get_posts_relation()->get_all();
    }

//    /**
//     * @param int $wp_id
//     * @return $this
//     * @throws Kohana_Exception
//     */
//    public function find_by_wp_id($wp_id)
//    {
//        $model = $this
//            ->model_factory()
//            ->filter_wp_id($wp_id)
//            ->find();
//
//        if (!$model->loaded()) {
//            $model->clear();
//        }
//
//        return $model;
//    }

    public function filter_is_active($value = TRUE)
    {
        return $this->where($this->object_column('is_active'), '=', $value);
    }

    public function order_by_place($desc = FALSE)
    {
        return $this->order_by($this->object_column('place'), $desc ? 'desc' : 'asc');
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
    protected function customFilterForSearchByUrl(UrlParametersInterface $parameters)
    {
        $parent_category = $parameters->getEntityByClassName($this);

        $this->filter_is_active()->filter_parent($parent_category);
    }
}
