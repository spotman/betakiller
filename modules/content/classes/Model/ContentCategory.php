<?php

class Model_ContentCategory extends \BetaKiller\Utils\Kohana\TreeModel
{
    use Model_ORM_ImportedFromWordpressTrait;

    const URL_PARAM = 'ContentCategory';

    // TODO title and description per category

    protected function _initialize()
    {
        $this->has_many([
            'articles'          =>  [
                'model'         =>  'Article',
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

    public function get_public_url()
    {
        /** @var \V2017\IFace\Article\Category\Item $iface */
        $iface = $this->iface_from_codename('Article\\Category\\Item');

        $params = $this->url_parameters_instance()
            ->set($this::URL_PARAM, $this);

        return $iface->url($params);
    }

    /**
     * @return Database_Result|Model_ContentArticle[]
     */
    public function get_all_related_articles()
    {
        // Collect all children categories
        $ids = $this->get_all_children($this->primary_key());

        // Add current category
        $ids[] = $this->get_id();

        return $ids
            ? $this->get_articles_relation()->model_factory()->filter_category_ids($ids)->get_all()
            : [];
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
            ->find_by_wp_id($wp_id)
            ->find();

        if (!$model->loaded())
        {
            $model->clear();
        }

        return $model;
    }

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
    protected function additional_tree_model_filtering()
    {
        return $this->filter_is_active()->order_by_place();
    }

    public function link_articles(array $articles_ids)
    {
        return $this->link_related('articles', $articles_ids);
    }

    /**
     * @return Model_ContentArticle
     */
    protected function get_articles_relation()
    {
        return $this->get('articles');
    }

    /**
     * @param URL_Parameters $parameters
     */
    protected function custom_find_by_url_filter(URL_Parameters $parameters)
    {
        $parent_category = $parameters->get(self::URL_PARAM);

        $this->filter_parent($parent_category);
    }
}
